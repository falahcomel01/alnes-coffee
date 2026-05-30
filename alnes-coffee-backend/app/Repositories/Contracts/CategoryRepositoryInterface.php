<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Category;
    public function findOrFail(int $id): Category;
    public function findBySlug(string $slug): ?Category;
    public function getActive(): Collection;
    public function getActiveWithProductCount(): Collection;
    public function getByType(string $type): Collection;
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Category;
    public function update(int $id, array $data): Category;
    public function delete(int $id): bool;
}