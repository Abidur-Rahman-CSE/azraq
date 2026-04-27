<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HomepageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'cta_label' => ['nullable', 'string', 'max:100'],
            'cta_href' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_enabled' => ['nullable', 'boolean'],
            'desktop_image_upload' => ['nullable', 'image', 'max:10240'],
            'mobile_image_upload' => ['nullable', 'image', 'max:10240'],
            'settings' => ['nullable', 'array'],
            'settings.desktop_image_url' => ['nullable', 'string', 'max:2048'],
            'settings.mobile_image_url' => ['nullable', 'string', 'max:2048'],
            'settings.selected_collection_ids' => ['nullable', 'array'],
            'settings.selected_collection_ids.*' => ['integer', 'exists:collections,id'],
            'settings.selected_product_ids' => ['nullable', 'array'],
            'settings.selected_product_ids.*' => ['integer', 'exists:products,id'],
            'settings.selected_category_ids' => ['nullable', 'array'],
            'settings.selected_category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
