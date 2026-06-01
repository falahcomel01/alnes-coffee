<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyRule extends Model
{
    protected $fillable = [
        'branch_id', 'created_by', 'name', 'type',
        'earn_per_amount', 'minimum_transaction',
        'multiplier', 'is_active',
    ];

    protected $casts = [
        'earn_per_amount'      => 'decimal:2',
        'minimum_transaction'  => 'decimal:2',
        'multiplier'           => 'decimal:2',
        'is_active'            => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class, 'rule_id');
    }

    // Hitung poin dari nominal transaksi
    public function calculatePoints(float $amount): int
    {
        if ($amount < $this->minimum_transaction) return 0;

        $points = floor($amount / $this->earn_per_amount);

        return (int) floor($points * $this->multiplier);
    }
}