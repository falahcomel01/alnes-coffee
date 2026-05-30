<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        return [new Channel('kitchen')];
    }

    public function broadcastAs(): string
    {
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id'             => $this->order->id,
            'invoice_number' => $this->order->invoice_number,
            'table_number'   => $this->order->table?->table_number ?? 'Takeaway',
            'customer_name'  => $this->order->customer_name,
            'order_status'   => $this->order->order_status->value,
            'grand_total'    => $this->order->grand_total,
            'ordered_at'     => $this->order->ordered_at->format('H:i'),
            'items_count'    => $this->order->items()->count(),
        ];
    }
}