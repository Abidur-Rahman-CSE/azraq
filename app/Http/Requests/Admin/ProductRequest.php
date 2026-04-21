<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'save_mode' => ['nullable', Rule::in(['draft', 'publish'])],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($productId)],
            'type' => ['required', Rule::enum(ProductType::class)],
            'status' => ['required', 'string', 'max:50'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'gte:price'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'manage_stock' => ['nullable', 'boolean'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'featured_image_upload' => ['nullable', 'image', 'max:5120'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'proof_notes_enabled' => ['nullable', 'boolean'],
            'font_presets_enabled' => ['nullable', 'boolean'],
            'assigned_template_id' => ['nullable', 'exists:personalization_templates,id'],
            'allowed_mockup_ids' => ['nullable', 'array'],
            'allowed_mockup_ids.*' => ['integer', 'exists:personalization_mockups,id'],
            'default_mockup_id' => ['nullable', 'integer', 'exists:personalization_mockups,id'],
            'gallery_default_source' => ['nullable', Rule::in(['template_flat_preview', 'selected_mockup', 'manual_featured_image'])],
            'show_flat_preview_first' => ['nullable', 'boolean'],
            'include_mockup_gallery' => ['nullable', 'boolean'],
            'live_preview_enabled' => ['nullable', 'boolean'],
            'personalization_help_text' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'gallery_uploads' => ['nullable', 'array'],
            'gallery_uploads.*' => ['image', 'max:5120'],
            'existing_images' => ['nullable', 'array'],
            'existing_images.*.label' => ['nullable', 'string', 'max:100'],
            'existing_images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'existing_images.*.position' => ['nullable', 'integer', 'min:0'],
            'existing_images.*.is_primary' => ['nullable', 'boolean'],
            'existing_images.*.remove' => ['nullable', 'boolean'],
            'collection_ids' => ['nullable', 'array'],
            'collection_ids.*' => ['integer', 'exists:collections,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'related_product_ids' => ['nullable', 'array'],
            'related_product_ids.*' => ['integer', 'exists:products,id'],
            'related_category_ids' => ['nullable', 'array'],
            'related_category_ids.*' => ['integer', 'exists:categories,id'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.option_values' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_default' => ['nullable', 'boolean'],
            'bundle_items' => ['nullable', 'array'],
            'bundle_items.*.child_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'bundle_items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'service_meta.service_type' => ['nullable', 'string', 'max:255'],
            'service_meta.duration_label' => ['nullable', 'string', 'max:255'],
            'service_meta.location_scope' => ['nullable', 'string', 'max:255'],
            'service_meta.requires_advance_payment' => ['nullable', 'boolean'],
            'service_meta.advance_payment_amount' => ['nullable', 'numeric', 'min:0'],
            'service_meta.booking_notes' => ['nullable', 'string'],
        ];
    }
}
