<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\PromoRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PromoRepositoryInterface $promoRepository,
        private readonly ProductService           $productService,
    ) {}

    public function createOrder(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $setting  = Setting::instance();
            $subtotal = 0;
            $items    = [];

            foreach ($data['items'] as $item) {
                $product      = $this->productService->getProductById($item['product_id']);
                $itemSubtotal = $product->price * $item['qty'];
                $subtotal    += $itemSubtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'qty'        => $item['qty'],
                    'price'      => $product->price,
                    'subtotal'   => $itemSubtotal,
                    'notes'      => $item['notes'] ?? null,
                ];
            }

            // Hitung promo
            $discount = 0;
            $promo    = null;
            if (!empty($data['promo_code'])) {
                $promo = $this->promoRepository->findActiveByCode($data['promo_code']);

                if ($promo) {
                    if ($promo->usage_limit && $promo->used_count >= $promo->usage_limit) {
                        throw new \Exception('Kode promo sudah mencapai batas penggunaan.');
                    }
                    if ($subtotal < $promo->minimum_purchase) {
                        throw new \Exception('Minimum pembelian Rp ' . number_format($promo->minimum_purchase, 0, ',', '.'));
                    }
                    $discount = $promo->type === 'percentage'
                        ? ($subtotal * $promo->value / 100)
                        : $promo->value;
                }
            }

            $tax        = $subtotal * ($setting->tax_percentage / 100);
            $serviceFee = $setting->service_fee;
            $grandTotal = $subtotal + $tax + $serviceFee - $discount;

            $order = $this->orderRepository->create([
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'table_id'       => $data['table_id'],
                'promo_id'       => $promo?->id,
                'customer_name'  => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'order_type'     => $data['order_type'],
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'order_status'   => 'pending',
                'subtotal'       => $subtotal,
                'tax'            => $tax,
                'service_fee'    => $serviceFee,
                'discount'       => $discount,
                'grand_total'    => $grandTotal,
                'notes'          => $data['notes'] ?? null,
                'ordered_at'     => now(),
            ]);

            foreach ($items as $item) {
                OrderItem::create(['order_id' => $order->id, ...$item]);
            }

            if ($promo) {
                $this->promoRepository->incrementUsage($promo->id);
            }

            // Broadcast realtime event
            OrderCreated::dispatch($order->load(['items.product', 'table']));

            return [
                'invoice_number' => $order->invoice_number,
                'order_status'   => $order->order_status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'grand_total'    => $order->grand_total,
            ];
        });
    }

    /**
     * @return Order|null
     */
    public function getOrderByInvoice(string $invoice): ?Order
    {
        return $this->orderRepository->findByInvoice($invoice);
    }
}