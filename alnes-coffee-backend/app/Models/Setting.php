<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'cafe_name', 'logo', 'address', 'phone', 'email',
        'instagram', 'facebook', 'tiktok', 'maps_url',
        'open_time', 'close_time', 'tax_percentage', 'service_fee', 'is_open',
    ];

    protected function casts(): array
    {
        return [
            'tax_percentage' => 'decimal:2',
            'service_fee'    => 'decimal:2',
            'is_open'        => 'boolean',
        ];
    }

    public static function instance(): static
    {
        return static::firstOrCreate(['id' => 1], [
            'cafe_name'      => 'Alnes Coffee',
            'open_time'      => '07:00:00',
            'close_time'     => '22:00:00',
            'tax_percentage' => 0,
            'service_fee'    => 1000,
            'is_open'        => true,
        ]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) return null;
        if (str_starts_with($this->logo, 'http')) return $this->logo;
        return asset('storage/' . $this->logo);
    }

    public function isCurrentlyOpen(): bool
    {
        if (!$this->is_open) return false;
        $now = now()->format('H:i');
        return $now >= $this->open_time && $now <= $this->close_time;
    }
}