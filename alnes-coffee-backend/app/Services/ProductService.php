<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function getMenuProducts(array $filters = []): LengthAwarePaginator
    {
        $filters['is_active'] = true;
        return $this->productRepository->paginateWithFilters($filters, 20);
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->paginateWithFilters($filters, $perPage);
    }

    public function getProductBySlug(string $slug): ?Product
    {
        return $this->productRepository->findBySlug($slug);
    }

    public function getProductById(int $id): Product
{
    return $this->productRepository->findOrFail($id);
}

    public function getFeaturedProducts(): array
    {
        return [
            'best_sellers' => $this->productRepository->getBestSellers(8),
            'popular'      => $this->productRepository->getPopular(8),
            'special'      => $this->productRepository->getSpecial(4),
            'recommended'  => $this->productRepository->getRecommended(8),
        ];
    }

    public function searchProducts(string $keyword): Collection
    {
        return $this->productRepository->search($keyword);
    }

    public function createProduct(array $data, ?UploadedFile $image = null): Product
    {
        $data['slug'] = $this->generateUniqueSlug($data['name'], $data['slug'] ?? null);
        if ($image) $data['image'] = $image->store('products', 'public');
        return $this->productRepository->create($data);
    }

    public function updateProduct(int $id, array $data, ?UploadedFile $image = null): Product
    {
        $product = $this->productRepository->findOrFail($id);
        if (!empty($data['name']) && empty($data['slug']) && $product->name !== $data['name']) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], null, $id);
        }
        if ($image) {
            if ($product->image && !str_starts_with($product->image, 'http')) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $image->store('products', 'public');
        }
        return $this->productRepository->update($id, $data);
    }

    public function deleteProduct(int $id): bool
    {
        $product = $this->productRepository->findOrFail($id);
        if ($product->image && !str_starts_with($product->image, 'http')) {
            Storage::disk('public')->delete($product->image);
        }
        return $this->productRepository->delete($id);
    }

    private function generateUniqueSlug(string $name, ?string $custom = null, ?int $excludeId = null): string
    {
        $base = Str::slug($custom ?? $name);
        $slug = $base;
        $i    = 1;
        while (true) {
            $existing = $this->productRepository->findBySlug($slug);
            if (!$existing || ($excludeId && $existing->id === $excludeId)) break;
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}