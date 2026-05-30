<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $orders = Order::with(['items.product', 'table'])
            ->whereIn('order_status', [
                OrderStatus::Pending,
                OrderStatus::Confirmed,
                OrderStatus::Cooking,
                OrderStatus::Ready,
            ])
            ->whereDate('ordered_at', today())
            ->orderBy('ordered_at')
            ->get()
            ->map(fn ($order) => [
                'id'             => $order->id,
                'invoice_number' => $order->invoice_number,
                'table_number'   => $order->table?->table_number ?? 'Takeaway',
                'customer_name'  => $order->customer_name,
                'order_status'   => $order->order_status->value,
                'order_type'     => $order->order_type->value,
                'notes'          => $order->notes,
                'ordered_at'     => $order->ordered_at->format('H:i'),
                'items'          => $order->items->map(fn ($item) => [
                    'name'  => $item->product?->name ?? '-',
                    'qty'   => $item->qty,
                    'notes' => $item->notes,
                ]),
            ]);

        return $this->successResponse(
            data: $orders,
            message: 'Data kitchen berhasil diambil.'
        );
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:confirmed,cooking,ready,completed'],
        ]);

        $order = Order::findOrFail($id);

        $timestamps = [];
        if ($request->status === 'completed') {
            $timestamps['completed_at'] = now();
        }

        $order->update([
            'order_status' => $request->status,
            ...$timestamps,
        ]);

        // Broadcast realtime event
        OrderStatusUpdated::dispatch($order->fresh());
    
        return $this->successResponse(
            data: ['order_status' => $order->order_status->value],
            message: 'Status pesanan berhasil diperbarui.'
        );
    }
}