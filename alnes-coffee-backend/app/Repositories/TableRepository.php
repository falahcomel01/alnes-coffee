<?php

namespace App\Repositories;

use App\Models\CafeTable;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TableRepository implements TableRepositoryInterface
{
    public function __construct(private readonly CafeTable $model) {}

    public function findBySlug(string $slug): ?CafeTable
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function all(): Collection
    {
        return $this->model->orderBy('table_number')->get();
    }
}