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
            'background_image_upload' => ['nullable', 'image', 'max:10240'],

            'settings' => ['nullable', 'array'],

            // Hero — single image (legacy/fallback)
            'settings.desktop_image_url' => ['nullable', 'string', 'max:2048'],
            'settings.mobile_image_url' => ['nullable', 'string', 'max:2048'],
            'settings.secondary_cta_label' => ['nullable', 'string', 'max:100'],
            'settings.secondary_cta_href' => ['nullable', 'string', 'max:255'],
            'settings.featured_product_id' => ['nullable', 'integer', 'exists:products,id'],

            // Hero slides (carousel)
            'settings.slides' => ['nullable', 'array', 'max:6'],
            'settings.slides.*.title' => ['nullable', 'string', 'max:200'],
            'settings.slides.*.subtitle' => ['nullable', 'string', 'max:120'],
            'settings.slides.*.body' => ['nullable', 'string', 'max:400'],
            'settings.slides.*.cta_label' => ['nullable', 'string', 'max:80'],
            'settings.slides.*.cta_href' => ['nullable', 'string', 'max:255'],
            'settings.slides.*.cta2_label' => ['nullable', 'string', 'max:80'],
            'settings.slides.*.cta2_href' => ['nullable', 'string', 'max:255'],
            'settings.slides.*.image_url' => ['nullable', 'string', 'max:2048'],
            'slide_images' => ['nullable', 'array'],
            'slide_images.*' => ['nullable', 'image', 'max:10240'],

            // Featured collections / products / categories
            'settings.selected_collection_ids' => ['nullable', 'array'],
            'settings.selected_collection_ids.*' => ['integer', 'exists:collections,id'],
            'settings.selected_product_ids' => ['nullable', 'array'],
            'settings.selected_product_ids.*' => ['integer', 'exists:products,id'],
            'settings.selected_category_ids' => ['nullable', 'array'],
            'settings.selected_category_ids.*' => ['integer', 'exists:categories,id'],

            // Stats strip
            'settings.stats' => ['nullable', 'array', 'max:6'],
            'settings.stats.*.num' => ['nullable', 'string', 'max:32'],
            'settings.stats.*.label' => ['nullable', 'string', 'max:80'],

            // Signature Nikah spotlight
            'settings.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'settings.process_steps' => ['nullable', 'array', 'max:6'],
            'settings.process_steps.*' => ['nullable', 'string', 'max:120'],

            // Atelier services
            'settings.service_ids' => ['nullable', 'array'],
            'settings.service_ids.*' => ['integer', 'exists:products,id'],

            // Finale CTA
            'settings.background_image_url' => ['nullable', 'string', 'max:2048'],

            // Instagram strip
            'settings.posts' => ['nullable', 'array', 'max:12'],
            'settings.posts.*.image_url' => ['nullable', 'string', 'max:2048'],
            'settings.posts.*.href' => ['nullable', 'string', 'max:2048'],

            // Trust strip
            'settings.signals' => ['nullable', 'array', 'max:6'],
            'settings.signals.*.icon' => ['nullable', 'string', 'max:8'],
            'settings.signals.*.label' => ['nullable', 'string', 'max:80'],
        ];
    }
}
