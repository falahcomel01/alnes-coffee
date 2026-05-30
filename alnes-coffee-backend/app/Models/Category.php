<?php

namespace App\Models;

use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'type', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'type'       => CategoryType::class,
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    public function scopeActive($query)        { return $query->where('is_active', true); }
    public function scopeOrdered($query)       { return $query->orderBy('sort_order', 'asc'); }
    public function scopeOfType($query, $type) { return $query->where('type', $type); }
}