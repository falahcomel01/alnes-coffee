<?php

namespace App\Repositories\Contracts;

use App\Models\Promo;

interface PromoRepositoryInterface
{
    public function findActiveByCode(string $code): ?Promo;
    public function incrementUsage(int $id): void;
}