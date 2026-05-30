<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(private readonly Order $model) {}

    public function findByInvoice(string $invoice): ?Order
    {
        return $this->model
            ->with(['items.product', 'table'])
            ->where('invoice_number', $invoice)
            ->first();
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function updateStatus(int $id, string $orderStatus): Order
    {
        $order = $this->model->findOrFail($id);
        $order->update(['order_status' => $orderStatus]);
        return $order->fresh();
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['table', 'items']);

        if (!empty($filters['order_status']))
            $query->where('order_status', $filters['order_status']);

        if (!empty($filters['payment_status']))
            $query->where('payment_status', $filters['payment_status']);

        if (!empty($filters['search']))
            $query->where('invoice_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('customer_name', 'like', '%' . $filters['search'] . '%');

        return $query->orderByDesc('ordered_at')->paginate($perPage);
    }
}