<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PromoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PromoService $promoService) {}

    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'code'     => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $result = $this->promoService->checkPromo(
                code: $request->code,
                subtotal: $request->subtotal
            );

            return $this->successResponse(
                data: $result,
                message: 'Promo berhasil digunakan.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}