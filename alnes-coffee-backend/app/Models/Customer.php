<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'phone', 'email',
        'points_balance', 'total_points_earned',
        'tier', 'tier_updated_at', 'is_active',
    ];

    protected $casts = [
        'tier_updated_at' => 'datetime',
        'is_active'       => 'boolean',
    ];

    // Tier thresholds
    const TIER_THRESHOLDS = [
        'bronze'   => 0,
        'silver'   => 1000,
        'gold'     => 5000,
        'platinum' => 15000,
    ];

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function redemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Auto update tier berdasarkan total_points_earned
    public function recalculateTier(): void
    {
        $total = $this->total_points_earned;

        $tier = 'bronze';
        foreach (self::TIER_THRESHOLDS as $name => $min) {
            if ($total >= $min) $tier = $name;
        }

        if ($this->tier !== $tier) {
            $this->update(['tier' => $tier, 'tier_updated_at' => now()]);
        }
    }
}