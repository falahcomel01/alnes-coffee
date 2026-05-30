<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'category_id'     => $this->category_id,
            'category'        => new CategoryResource($this->whenLoaded('category')),
            'name'            => $this->name,
            'slug'            => $this->slug,
            'description'     => $this->description,
            'image_url'       => $this->image_url,
            'price'           => (float) $this->price,
            'formatted_price' => $this->formatted_price,
            'stock'           => $this->stock,
            'in_stock'        => $this->is_in_stock,
            'sku'             => $this->sku,
            'is_best_seller'  => $this->is_best_seller,
            'is_special'      => $this->is_special,
            'is_popular'      => $this->is_popular,
            'is_recommended'  => $this->is_recommended,
            'is_active'       => $this->is_active,
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
        ];
    }
}