<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id', 'session_token', 'customer_name',
        'customer_phone', 'started_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function table(): BelongsTo  { return $this->belongsTo(CafeTable::class, 'table_id'); }
    public function scopeActive($query) { return $query->whereNull('ended_at'); }
    public function isActive(): bool    { return $this->ended_at === null; }
    public function end(): void         { $this->update(['ended_at' => now()]); }
}