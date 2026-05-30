<?php

namespace App\Repositories\Contracts;

use App\Models\CafeTable;

interface TableRepositoryInterface
{
    public function findBySlug(string $slug): ?CafeTable;
    public function all(): \Illuminate\Database\Eloquent\Collection;
}