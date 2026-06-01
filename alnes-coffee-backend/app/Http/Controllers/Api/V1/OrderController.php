<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly OrderService $orderService) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'table_id'           => ['required', 'exists:cafe_tables,id'],
            'customer_name'      => ['required', 'string', 'max:255'],
            'customer_phone'     => ['required', 'string', 'max:20'],
            'order_type'         => ['required', 'in:dine_in,takeaway'],
            'payment_method'     => ['required', 'in:qris,cash,transfer'],
            'promo_code'         => ['nullable', 'string'],
            'notes'              => ['nullable', 'string'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'items.*.notes'      => ['nullable', 'string'],
        ]);

        try {
            $result = $this->orderService->createOrder($request->all());
            return $this->createdResponse(
                data: $result,
                message: 'Pesanan berhasil dibuat!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function show(string $invoice): JsonResponse
    {
        $order = $this->orderService->getOrderByInvoice($invoice);

        if (!$order) {
            return $this->notFoundResponse('Pesanan tidak ditemukan.');
        }

        return $this->successResponse(
            data: [
                'invoice_number' => $order->invoice_number,
                'table_number'   => $order->table->table_number,
                'customer_name'  => $order->customer_name,
                'order_type'     => $order->order_type,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status'   => $order->order_status,
                'subtotal'       => $order->subtotal,
                'tax'            => $order->tax,
                'service_fee'    => $order->service_fee,
                'discount'       => $order->discount,
                'grand_total'    => $order->grand_total,
                'notes'          => $order->notes,
                'ordered_at'     => $order->ordered_at,
                'items'          => $order->items->map(fn ($item) => [
                    'product_name'  => $item->product->name,
                    'product_image' => $item->product->image,
                    'qty'           => $item->qty,
                    'price'         => $item->price,
                    'subtotal'      => $item->subtotal,
                    'notes'         => $item->notes,
                ]),
            ],
            message: 'Detail pesanan berhasil diambil.'
        );
    }
}