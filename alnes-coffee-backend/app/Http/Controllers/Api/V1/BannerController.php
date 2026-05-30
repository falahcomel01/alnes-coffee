<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($banner) => [
                'id'    => $banner->id,
                'title' => $banner->title,
                'image' => $banner->image
                    ? (str_starts_with($banner->image, 'http')
                        ? $banner->image
                        : asset('storage/' . $banner->image))
                    : null,
                'link'  => $banner->link,
            ]);

        return $this->successResponse(
            data: $banners,
            message: 'Banner berhasil diambil.'
        );
    }
}