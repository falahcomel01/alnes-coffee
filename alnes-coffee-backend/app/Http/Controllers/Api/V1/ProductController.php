<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'category_id', 'category_slug', 'search',
            'is_best_seller', 'is_popular', 'is_special', 'is_recommended',
            'sort_by', 'sort_order', 'min_price', 'max_price',
        ]);

        $products = $this->productService->getMenuProducts($filters);

        return $this->paginatedResponse(
            collection: ProductResource::collection($products),
            message: 'Produk berhasil diambil.'
        );
    }

    public function featured(): JsonResponse
    {
        $featured = $this->productService->getFeaturedProducts();

        return $this->successResponse(
            data: [
                'best_sellers' => ProductResource::collection($featured['best_sellers']),
                'popular'      => ProductResource::collection($featured['popular']),
                'special'      => ProductResource::collection($featured['special']),
                'recommended'  => ProductResource::collection($featured['recommended']),
            ],
            message: 'Produk unggulan berhasil diambil.'
        );
    }

    public function search(Request $request): JsonResponse
    {
        $keyword = $request->input('q', '');

        if (strlen($keyword) < 2) {
            return $this->successResponse(
                data: [],
                message: 'Masukkan minimal 2 karakter.'
            );
        }

        $products = $this->productService->searchProducts($keyword);

        return $this->successResponse(
            data: ProductResource::collection($products),
            message: 'Hasil pencarian produk.'
        );
    }

    public function show(string $slug): JsonResponse
    {
        $product = $this->productService->getProductBySlug($slug);

        if (!$product) {
            return $this->notFoundResponse('Produk tidak ditemukan.');
        }

        return $this->successResponse(
            data: new ProductResource($product),
            message: 'Produk berhasil diambil.'
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct(
            data: $request->validated(),
            image: $request->file('image')
        );

        return $this->createdResponse(
            data: new ProductResource($product),
            message: 'Produk berhasil dibuat.'
        );
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $product = $this->productService->updateProduct(
                id: $id,
                data: $request->validated(),
                image: $request->file('image')
            );

            return $this->successResponse(
                data: new ProductResource($product),
                message: 'Produk berhasil diperbarui.'
            );
        } catch (\Exception $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productService->deleteProduct($id);
            return $this->noContentResponse('Produk berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}