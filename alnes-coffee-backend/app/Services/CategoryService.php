<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getActiveCategories(): Collection
    {
        return $this->categoryRepository->getActiveWithProductCount();
    }

    public function getCategoryBySlug(string $slug): ?Category
    {
        return $this->categoryRepository->findBySlug($slug);
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->categoryRepository->paginateWithFilters($filters, $perPage);
    }

    public function createCategory(array $data): Category
    {
        $data['slug'] = $this->generateUniqueSlug($data['name'], $data['slug'] ?? null);
        return $this->categoryRepository->create($data);
    }

    public function updateCategory(int $id, array $data): Category
    {
        if (!empty($data['name']) && empty($data['slug'])) {
            $existing = $this->categoryRepository->findOrFail($id);
            if ($existing->name !== $data['name']) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], null, $id);
            }
        }
        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }

    private function generateUniqueSlug(string $name, ?string $custom = null, ?int $excludeId = null): string
    {
        $base = Str::slug($custom ?? $name);
        $slug = $base;
        $i    = 1;
        while (true) {
            $existing = $this->categoryRepository->findBySlug($slug);
            if (!$existing || ($excludeId && $existing->id === $excludeId)) break;
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}