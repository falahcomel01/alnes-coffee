<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'table_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_date',
        'reservation_time',
        'guest_count',
        'status',
        'notes',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'reservation_time' => 'datetime:H:i',
        'confirmed_at'     => 'datetime',
        'cancelled_at'     => 'datetime',
        'guest_count'      => 'integer',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(CafeTable::class, 'table_id');
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isConfirmed(): bool { return $this->status === 'confirmed'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    public function scopeUpcoming($query)
    {
        return $query->where('reservation_date', '>=', today())
                     ->whereIn('status', ['pending', 'confirmed'])
                     ->orderBy('reservation_date')
                     ->orderBy('reservation_time');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('reservation_date', today());
    }
}