<?php

namespace App\Models;

use App\Enums\{OrderStatus, OrderType, PaymentMethod, PaymentStatus};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'table_id', 'promo_id', 'customer_name', 'customer_phone',
        'order_type', 'payment_method', 'payment_status', 'order_status',
        'subtotal', 'tax', 'service_fee', 'discount', 'grand_total',
        'notes', 'ordered_at', 'paid_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'order_type'     => OrderType::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'order_status'   => OrderStatus::class,
            'subtotal'       => 'decimal:2',
            'tax'            => 'decimal:2',
            'service_fee'    => 'decimal:2',
            'discount'       => 'decimal:2',
            'grand_total'    => 'decimal:2',
            'ordered_at'     => 'datetime',
            'paid_at'        => 'datetime',
            'completed_at'   => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->invoice_number)) {
                $order->invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }

            if (empty($order->ordered_at)) {
                $order->ordered_at = now();
            }
        });
    }

    public function table(): BelongsTo      { return $this->belongsTo(CafeTable::class, 'table_id'); }
    public function promo(): BelongsTo      { return $this->belongsTo(Promo::class); }
    public function items(): HasMany        { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany     { return $this->hasMany(Payment::class); }
    public function latestPayment(): HasOne { return $this->hasOne(Payment::class)->latestOfMany(); }

    public function scopePending($query) { return $query->where('order_status', OrderStatus::Pending->value); }
    public function scopeActive($query)  { return $query->whereNotIn('order_status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value]); }
    public function scopePaid($query)    { return $query->where('payment_status', PaymentStatus::Paid->value); }
    public function scopeToday($query)   { return $query->whereDate('ordered_at', today()); }

    public function isPaid(): bool         { return $this->payment_status === PaymentStatus::Paid; }
    public function canBeCancelled(): bool { return $this->order_status->canBeCancelled() && !$this->isPaid(); }
}