<?php

namespace App\Models;

use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CafeTable extends Model
{
    use HasFactory;

    protected $table    = 'cafe_tables';
    protected $fillable = ['table_number', 'slug', 'qr_code', 'status'];

    protected function casts(): array
    {
        return ['status' => TableStatus::class];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function customerSessions(): HasMany
    {
        return $this->hasMany(CustomerSession::class, 'table_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', TableStatus::Available->value);
    }

    public function isAvailable(): bool     { return $this->status === TableStatus::Available; }
    public function markAsOccupied(): void  { $this->update(['status' => TableStatus::Occupied]); }
    public function markAsAvailable(): void { $this->update(['status' => TableStatus::Available]); }

    public function getQrUrlAttribute(): string
    {
        return config('app.url') . '/table/' . $this->slug;
    }
}