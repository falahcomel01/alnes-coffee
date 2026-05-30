<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function findByInvoice(string $invoice): ?Order;
    public function create(array $data): Order;
    public function updateStatus(int $id, string $orderStatus): Order;
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}