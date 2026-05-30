<?php

namespace App\Services;

use App\Models\CafeTable;
use App\Repositories\Contracts\TableRepositoryInterface;

class TableService
{
    public function __construct(
        private readonly TableRepositoryInterface $tableRepository
    ) {}

    public function getTableBySlug(string $slug): ?CafeTable
    {
        return $this->tableRepository->findBySlug($slug);
    }
}