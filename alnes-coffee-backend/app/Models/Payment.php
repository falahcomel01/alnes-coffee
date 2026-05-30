<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'payment_gateway', 'transaction_id',
        'payment_type', 'amount', 'status', 'payload', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status'  => PaymentStatus::class,
            'amount'  => 'decimal:2',
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function isPaid(): bool     { return $this->status === PaymentStatus::Paid; }
}