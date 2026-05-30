<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'title', 'type', 'value', 'minimum_purchase',
        'maximum_discount', 'expired_at', 'usage_limit', 'used_count', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'            => 'decimal:2',
            'minimum_purchase' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'expired_at'       => 'datetime',
            'usage_limit'      => 'integer',
            'used_count'       => 'integer',
            'is_active'        => 'boolean',
        ];
    }

    public function orders(): HasMany { return $this->hasMany(Order::class); }

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expired_at')->orWhere('expired_at', '>', now()))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expired_at && $this->expired_at->isPast()) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal < $this->minimum_purchase) return 0;
        if ($this->type === 'percentage') {
            $discount = $subtotal * ($this->value / 100);
            return $this->maximum_discount ? min($discount, $this->maximum_discount) : $discount;
        }
        return min($this->value, $subtotal);
    }
}