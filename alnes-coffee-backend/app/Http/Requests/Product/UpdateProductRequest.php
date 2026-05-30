<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'category_id'    => ['sometimes', 'exists:categories,id'],
            'name'           => ['sometimes', 'string', 'max:150'],
            'slug'           => ['nullable', 'string', Rule::unique('products', 'slug')->ignore($id)],
            'description'    => ['nullable', 'string'],
            'image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'price'          => ['sometimes', 'numeric', 'min:0'],
            'stock'          => ['nullable', 'integer', 'min:0'],
            'sku'            => ['nullable', 'string', Rule::unique('products', 'sku')->ignore($id)],
            'is_best_seller' => ['sometimes', 'boolean'],
            'is_special'     => ['sometimes', 'boolean'],
            'is_popular'     => ['sometimes', 'boolean'],
            'is_recommended' => ['sometimes', 'boolean'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }
}