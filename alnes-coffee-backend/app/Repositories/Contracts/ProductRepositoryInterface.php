<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Product;
    public function findOrFail(int $id): Product;
    public function findBySlug(string $slug): ?Product;
    public function getActive(): Collection;
    public function getBestSellers(int $limit = 10): Collection;
    public function getPopular(int $limit = 10): Collection;
    public function getSpecial(int $limit = 10): Collection;
    public function getRecommended(int $limit = 10): Collection;
    public function getByCategory(int $categoryId): Collection;
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
    public function search(string $keyword): Collection;
    public function create(array $data): Product;
    public function update(int $id, array $data): Product;
    public function delete(int $id): bool;
}