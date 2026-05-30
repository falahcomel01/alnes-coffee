<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TableService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TableService $tableService) {}

    public function show(string $slug): JsonResponse
    {
        $table = $this->tableService->getTableBySlug($slug);

        if (!$table) {
            return $this->notFoundResponse('Meja tidak ditemukan.');
        }

        return $this->successResponse(
            data: [
                'id'           => $table->id,
                'table_number' => $table->table_number,
                'slug'         => $table->slug,
                'status'       => $table->status,
            ],
            message: 'Data meja berhasil diambil.'
        );
    }
}