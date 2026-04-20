<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'storefront_excerpt' => ['nullable', 'string', 'max:280'],
            'image_upload' => ['nullable', 'image', 'max:5120'],
            'banner_upload' => ['nullable', 'image', 'max:5120'],
            'mobile_banner_upload' => ['nullable', 'image', 'max:5120'],
            'icon_upload' => ['nullable', 'image', 'max:5120'],
            'seo_image_upload' => ['nullable', 'image', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'related_category_ids' => ['nullable', 'array'],
            'related_category_ids.*' => ['integer', 'exists:categories,id'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
