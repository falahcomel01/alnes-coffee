<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyRedemption extends Model
{
    protected $fillable = [
        'customer_id', 'loyalty_reward_id', 'order_id',
        'points_used', 'status', 'used_at', 'expired_at',
    ];

    protected $casts = [
        'used_at'    => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function reward()
    {
        return $this->belongsTo(LoyaltyReward::class, 'loyalty_reward_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}