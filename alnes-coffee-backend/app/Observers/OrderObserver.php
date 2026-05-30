<?php

namespace App\Observers;

use App\Models\CafeTable;
use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        if ($order->table_id) {
            CafeTable::where('id', $order->table_id)
                ->update(['status' => 'occupied']);
        }
    }

    public function updated(Order $order): void
    {
        if (in_array($order->order_status->value, ['completed', 'cancelled'])) {
            // Cek apakah masih ada order aktif di meja ini
            $activeOrders = Order::where('table_id', $order->table_id)
                ->whereNotIn('order_status', ['completed', 'cancelled'])
                ->where('id', '!=', $order->id)
                ->count();

            if ($activeOrders === 0) {
                CafeTable::where('id', $order->table_id)
                    ->update(['status' => 'available']);
            }
        }
    }
}