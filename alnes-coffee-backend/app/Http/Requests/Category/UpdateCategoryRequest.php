<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name'       => ['sometimes', 'string', 'max:255'],
            'slug'       => ['nullable', 'string', 'max:255', "unique:categories,slug,{$id}"],
            'icon'       => ['nullable', 'string', 'max:255'],
            'type'       => ['sometimes', 'in:food,beverages'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active'  => ['nullable', 'boolean'],
        ];
    }
}