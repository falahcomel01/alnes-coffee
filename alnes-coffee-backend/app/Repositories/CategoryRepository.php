<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private readonly Category $model) {}

    public function all(): Collection
    {
        return $this->model->orderBy('sort_order')->get();
    }

    public function find(int $id): ?Category
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Category
    {
        return $this->model->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function getActive(): Collection
    {
        return $this->model->active()->ordered()->get();
    }

    public function getActiveWithProductCount(): Collection
    {
        return $this->model->active()->ordered()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->get();
    }

    public function getByType(string $type): Collection
    {
        return $this->model->active()->ofType($type)->ordered()->get();
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (!empty($filters['type']))     $query->where('type', $filters['type']);
        if (isset($filters['is_active'])) $query->where('is_active', $filters['is_active']);
        if (!empty($filters['search']))   $query->where('name', 'like', '%' . $filters['search'] . '%');

        return $query->orderBy('sort_order')->paginate($perPage);
    }

    public function create(array $data): Category
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Category
    {
        $category = $this->findOrFail($id);
        $category->update($data);
        return $category->fresh();
    }

    public function delete(int $id): bool
    {
        $category = $this->findOrFail($id);
        if ($category->products()->where('is_active', true)->exists()) {
            throw new \Exception('Kategori masih memiliki produk aktif.');
        }
        return $category->delete();
    }
}