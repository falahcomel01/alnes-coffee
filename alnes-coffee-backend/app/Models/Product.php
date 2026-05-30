<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'image', 'price',
        'stock', 'sku', 'is_best_seller', 'is_special', 'is_popular',
        'is_recommended', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'is_best_seller' => 'boolean',
            'is_special'     => 'boolean',
            'is_popular'     => 'boolean',
            'is_recommended' => 'boolean',
            'is_active'      => 'boolean',
            'stock'          => 'integer',
        ];
    }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function orderItems(): HasMany  { return $this->hasMany(OrderItem::class); }

    public function scopeActive($query)      { return $query->where('is_active', true); }
    public function scopeBestSeller($query)  { return $query->where('is_active', true)->where('is_best_seller', true); }
    public function scopePopular($query)     { return $query->where('is_active', true)->where('is_popular', true); }
    public function scopeSpecial($query)     { return $query->where('is_active', true)->where('is_special', true); }
    public function scopeRecommended($query) { return $query->where('is_active', true)->where('is_recommended', true); }
    public function scopeByCategory($query, int $id) { return $query->where('category_id', $id); }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http')) return $this->image;
        return asset('storage/' . $this->image);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp' . number_format($this->price, 0, ',', '.');
    }

    public function getIsInStockAttribute(): bool { return $this->stock > 0; }
}