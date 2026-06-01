<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyReward extends Model
{
    protected $fillable = [
        'branch_id', 'created_by', 'name', 'description', 'image',
        'type', 'points_required', 'value', 'stock',
        'min_tier', 'expired_at', 'is_active',
    ];

    protected $casts = [
        'value'      => 'decimal:2',
        'expired_at' => 'datetime',
        'is_active'  => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function redemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }

    public function isAvailableForTier(string $tier): bool
    {
        if (!$this->min_tier) return true;

        $order = ['bronze' => 1, 'silver' => 2, 'gold' => 3, 'platinum' => 4];

        return ($order[$tier] ?? 0) >= ($order[$this->min_tier] ?? 0);
    }

    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function hasStock(): bool
    {
        return is_null($this->stock) || $this->stock > 0;
    }
}