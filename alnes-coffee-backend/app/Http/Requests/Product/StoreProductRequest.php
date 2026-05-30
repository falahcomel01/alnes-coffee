<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        return [
            'category_id'    => ['required', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:150'],
            'slug'           => ['nullable', 'string', 'max:180', 'unique:products,slug'],
            'description'    => ['nullable', 'string'],
            'image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'price'          => ['required', 'numeric', 'min:0'],
            'stock'          => ['nullable', 'integer', 'min:0'],
            'sku'            => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'is_best_seller' => ['boolean'],
            'is_special'     => ['boolean'],
            'is_popular'     => ['boolean'],
            'is_recommended' => ['boolean'],
            'is_active'      => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists'   => 'Kategori tidak ditemukan.',
            'name.required'        => 'Nama menu wajib diisi.',
            'price.required'       => 'Harga wajib diisi.',
            'image.max'            => 'Ukuran gambar maksimal 2MB.',
            'sku.unique'           => 'SKU sudah digunakan.',
        ];
    }
}