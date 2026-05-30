<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(private readonly Product $model) {}

    public function all(): Collection
    {
        return $this->model->with('category')->get();
    }

    public function find(int $id): ?Product
    {
        return $this->model->with('category')->find($id);
    }

    public function findOrFail(int $id): Product
    {
        return $this->model->with('category')->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->model->with('category')->where('slug', $slug)->first();
    }

    public function getActive(): Collection
    {
        return $this->model->active()->with('category')->get();
    }

    public function getBestSellers(int $limit = 10): Collection
    {
        return $this->model->bestSeller()->with('category')->limit($limit)->get();
    }

    public function getPopular(int $limit = 10): Collection
    {
        return $this->model->popular()->with('category')->limit($limit)->get();
    }

    public function getSpecial(int $limit = 10): Collection
    {
        return $this->model->special()->with('category')->limit($limit)->get();
    }

    public function getRecommended(int $limit = 10): Collection
    {
        return $this->model->recommended()->with('category')->limit($limit)->get();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->model->active()->byCategory($categoryId)->with('category')->get();
    }

    public function search(string $keyword): Collection
    {
        return $this->model->active()->with('category')
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->get();
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('category');

        if (!empty($filters['category_id'])) $query->where('category_id', $filters['category_id']);
        if (isset($filters['is_active']))    $query->where('is_active', $filters['is_active']);
        if (!empty($filters['search']))      $query->where('name', 'like', "%{$filters['search']}%");

        foreach (['is_best_seller', 'is_popular', 'is_special', 'is_recommended'] as $flag) {
            if (!empty($filters[$flag])) $query->where($flag, true);
        }

        return $query->orderBy($filters['sort_by'] ?? 'name', $filters['sort_order'] ?? 'asc')
                     ->paginate($perPage);
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->findOrFail($id);
        $product->update($data);
        return $product->fresh(['category']);
    }

    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }
}