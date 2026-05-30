<?php

namespace App\Services;

use App\Repositories\Contracts\PromoRepositoryInterface;

class PromoService
{
    public function __construct(
        private readonly PromoRepositoryInterface $promoRepository
    ) {}

    public function checkPromo(string $code, float $subtotal): array
    {
        $promo = $this->promoRepository->findActiveByCode($code);

        if (!$promo) {
            throw new \Exception('Kode promo tidak valid atau sudah expired.');
        }

        if ($promo->usage_limit && $promo->used_count >= $promo->usage_limit) {
            throw new \Exception('Kode promo sudah mencapai batas penggunaan.');
        }

        if ($subtotal < $promo->minimum_purchase) {
            throw new \Exception('Minimum pembelian Rp ' . number_format($promo->minimum_purchase, 0, ',', '.'));
        }

        $discount = $promo->type === 'percentage'
            ? ($subtotal * $promo->value / 100)
            : $promo->value;

        return [
            'code'     => $promo->code,
            'title'    => $promo->title,
            'type'     => $promo->type,
            'value'    => $promo->value,
            'discount' => $discount,
        ];
    }
}