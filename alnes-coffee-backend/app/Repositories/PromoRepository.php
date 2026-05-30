<?php

namespace App\Repositories;

use App\Models\Promo;
use App\Repositories\Contracts\PromoRepositoryInterface;

class PromoRepository implements PromoRepositoryInterface
{
    public function __construct(private readonly Promo $model) {}

    public function findActiveByCode(string $code): ?Promo
    {
        return $this->model
            ->where('code', $code)
            ->where('is_active', true)
            ->where('expired_at', '>', now())
            ->first();
    }

    public function incrementUsage(int $id): void
    {
        $this->model->where('id', $id)->increment('used_count');
    }
}