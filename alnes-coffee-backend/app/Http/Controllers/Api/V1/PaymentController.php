<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\LoyaltyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly LoyaltyService $loyaltyService)
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');
    }

    // Generate Snap Token — tidak berubah
    public function createToken(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_number' => ['required', 'string'],
        ]);

        $order = Order::with(['items.product', 'table'])
            ->where('invoice_number', $request->invoice_number)
            ->first();

        if (!$order) {
            return $this->notFoundResponse('Pesanan tidak ditemukan.');
        }

        if ($order->payment_status === 'paid') {
            return $this->errorResponse('Pesanan ini sudah dibayar.', 422);
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $order->invoice_number,
                'gross_amount' => (int) $order->grand_total,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'phone'      => $order->customer_phone,
            ],
            'item_details' => $order->items->map(fn($item) => [
                'id'       => $item->product_id,
                'price'    => (int) $item->price,
                'quantity' => $item->qty,
                'name'     => substr($item->product->name, 0, 50),
            ])->toArray(),
            'callbacks' => [
                'finish' => config('app.frontend_url') . '/order/' . $order->invoice_number,
            ],
        ];

        if ($order->service_fee > 0) {
            $params['item_details'][] = [
                'id' => 'SERVICE_FEE', 'price' => (int) $order->service_fee,
                'quantity' => 1, 'name' => 'Biaya Layanan',
            ];
        }

        if ($order->tax > 0) {
            $params['item_details'][] = [
                'id' => 'TAX', 'price' => (int) $order->tax,
                'quantity' => 1, 'name' => 'Pajak',
            ];
        }

        if ($order->discount > 0) {
            $params['item_details'][] = [
                'id' => 'DISCOUNT', 'price' => -(int) $order->discount,
                'quantity' => 1, 'name' => 'Diskon',
            ];
        }

        try {
            $snapToken = Snap::getSnapToken($params);

            return $this->successResponse(
                data: [
                    'snap_token'     => $snapToken,
                    'invoice_number' => $order->invoice_number,
                    'grand_total'    => $order->grand_total,
                    'client_key'     => config('midtrans.client_key'),
                ],
                message: 'Token pembayaran berhasil dibuat.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal membuat token pembayaran: ' . $e->getMessage(), 500);
        }
    }

    // Webhook dari Midtrans
    public function webhook(Request $request): JsonResponse
    {
        try {
            $notification = new Notification();

            $orderId           = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus       = $notification->fraud_status;
            $paymentType       = $notification->payment_type;
            $transactionId     = $notification->transaction_id;
            $grossAmount       = $notification->gross_amount;

            $order = Order::where('invoice_number', $orderId)->first();
            if (!$order) return response()->json(['message' => 'Order not found'], 404);

            $paymentStatus = 'pending';

            if ($transactionStatus === 'capture') {
                $paymentStatus = $fraudStatus === 'accept' ? 'paid' : 'failed';
            } elseif ($transactionStatus === 'settlement') {
                $paymentStatus = 'paid';
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $paymentStatus = 'failed';
            } elseif ($transactionStatus === 'refund') {
                $paymentStatus = 'refunded';
            }

            Payment::updateOrCreate(
                ['transaction_id' => $transactionId],
                [
                    'order_id'        => $order->id,
                    'payment_gateway' => 'midtrans',
                    'transaction_id'  => $transactionId,
                    'payment_type'    => $paymentType,
                    'amount'          => $grossAmount,
                    'status'          => $paymentStatus,
                    'payload'         => json_encode($request->all()),
                    'paid_at'         => $paymentStatus === 'paid' ? now() : null,
                ]
            );

            $orderUpdate = ['payment_status' => $paymentStatus];

            if ($paymentStatus === 'paid') {
                $orderUpdate['paid_at']      = now();
                $orderUpdate['order_status'] = 'confirmed';
                $order->update($orderUpdate);

                // ── Earn loyalty points ──────────────────────────
                $this->loyaltyService->earnPoints($order->fresh());

                // ── Broadcast realtime ───────────────────────────
                event(new \App\Events\OrderStatusUpdated($order->fresh()));
            } else {
                $order->update($orderUpdate);
            }

            return response()->json(['message' => 'OK']);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Konfirmasi pembayaran cash oleh cashier
    public function confirmCash(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_number' => ['required', 'string'],
        ]);

        $order = Order::where('invoice_number', $request->invoice_number)->first();

        if (!$order) {
            return $this->notFoundResponse('Pesanan tidak ditemukan.');
        }

        if ($order->payment_status === 'paid') {
            return $this->errorResponse('Pesanan ini sudah dibayar.', 422);
        }

        if ($order->payment_method !== 'cash') {
            return $this->errorResponse('Metode pembayaran bukan cash.', 422);
        }

        // Simpan ke tabel payments
        Payment::create([
            'order_id'        => $order->id,
            'payment_gateway' => 'manual',
            'payment_type'    => 'cash',
            'amount'          => $order->grand_total,
            'status'          => 'paid',
            'paid_at'         => now(),
        ]);

        $order->update([
            'payment_status' => 'paid',
            'paid_at'        => now(),
        ]);

        // ── Earn loyalty points ──────────────────────────────────
        $this->loyaltyService->earnPoints($order->fresh());

        // ── Broadcast realtime ───────────────────────────────────
        event(new \App\Events\OrderStatusUpdated($order->fresh()));

        return $this->successResponse(
            data: ['invoice_number' => $order->invoice_number],
            message: 'Pembayaran cash berhasil dikonfirmasi.'
        );
    }
}