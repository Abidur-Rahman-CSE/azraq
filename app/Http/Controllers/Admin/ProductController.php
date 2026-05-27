<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Collection;
use App\Models\PersonalizationTemplate;
use App\Models\PersonalizationMockup;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ServiceProductMeta;
use App\Models\Tag;
use App\Support\PersonalizationTemplateSnapshot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $filters = [
            'q' => request('q'),
            'type' => request('type'),
            'category_id' => request('category_id'),
            'customizable' => request()->boolean('customizable'),
            'nikah_only' => request()->boolean('nikah_only'),
            'stock_status' => request('stock_status'),
        ];

        $products = Product::query()
            ->with(['category', 'personalizationTemplate', 'personalizationMockups', 'images'])
            ->withCount('images')
            ->when(filled($filters['q']), function (Builder $query) use ($filters): void {
                $query->where(function (Builder $nested) use ($filters): void {
                    $nested
                        ->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('sku', 'like', '%'.$filters['q'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when(filled($filters['type']), fn (Builder $query) => $query->where('type', $filters['type']))
            ->when(filled($filters['category_id']), fn (Builder $query) => $query->where('category_id', $filters['category_id']))
            ->when($filters['customizable'], function (Builder $query): void {
                $query->whereIn('type', [
                    ProductType::LightCustomizable->value,
                    ProductType::AdvancedPersonalized->value,
                ]);
            })
            ->when($filters['nikah_only'], function (Builder $query): void {
                $query->where(function (Builder $nested): void {
                    $nested
                        ->where('type', ProductType::AdvancedPersonalized->value)
                        ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', 'like', '%nikah%'))
                        ->orWhere('name', 'like', '%nikah%');
                });
            })
            ->when(filled($filters['stock_status']), function (Builder $query) use ($filters): void {
                match ($filters['stock_status']) {
                    'low' => $query->where('manage_stock', true)->whereColumn('stock_quantity', '<=', 'low_stock_threshold'),
                    'out' => $query->where('manage_stock', true)->where('stock_quantity', 0),
                    'made_to_order' => $query->where('manage_stock', false),
                    default => null,
                };
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.catalog.products.index', [
            'products' => $products,
            'productTypes' => ProductType::options(),
            'categories' => Category::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('admin.catalog.products.create', $this->formData(new Product([
            'type' => ProductType::Standard,
            'status' => 'draft',
            'manage_stock' => true,
            'proof_notes_enabled' => true,
            'font_presets_enabled' => true,
        ])));
    }

    public function store(ProductRequest $request)
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::create($this->productPayload($request));
            $this->syncProductRelations($product, $request->validated());

            return $product;
        });

        return redirect()->route('admin.catalog.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        $product->load(['collections', 'tags', 'relatedProducts', 'relatedCategories', 'variants', 'bundleItems.childProduct', 'serviceMeta', 'images', 'personalizationTemplate.mockups', 'personalizationMockups']);

        return view('admin.catalog.products.edit', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product)
    {
        DB::transaction(function () use ($request, $product): void {
            $product->update($this->productPayload($request));
            $this->syncProductRelations($product, $request->validated());
        });

        return redirect()->route('admin.catalog.products.edit', $product)->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.catalog.products.index')->with('status', 'Product deleted.');
    }

    public function duplicate(Product $product)
    {
        $product->load([
            'collections',
            'tags',
            'relatedProducts',
            'relatedCategories',
            'variants',
            'bundleItems',
            'serviceMeta',
            'images',
            'personalizationTemplate.fields',
            'personalizationTemplate.fonts',
            'personalizationMockups',
        ]);

        $duplicate = DB::transaction(function () use ($product): Product {
            $copy = $product->replicate();
            $copy->name = $product->name.' Copy';
            $copy->slug = $this->uniqueProductSlug($product->slug.'-copy');
            $copy->sku = $this->uniqueProductSku($product->sku);
            $copy->status = 'draft';
            $copy->is_featured = false;
            $copy->push();

            $copy->collections()->sync($product->collections->pluck('id')->all());
            $copy->tags()->sync($product->tags->pluck('id')->all());
            $copy->relatedProducts()->sync($product->relatedProducts->pluck('id')->all());
            $copy->relatedCategories()->sync($product->relatedCategories->pluck('id')->all());

            foreach ($product->variants as $variant) {
                $variantCopy = $variant->replicate();
                $variantCopy->product_id = $copy->id;
                $variantCopy->save();
            }

            foreach ($product->images as $image) {
                $imageCopy = $image->replicate();
                $imageCopy->product_id = $copy->id;
                $imageCopy->save();
            }

            foreach ($product->bundleItems as $bundleItem) {
                $bundleItemCopy = $bundleItem->replicate();
                $bundleItemCopy->bundle_product_id = $copy->id;
                $bundleItemCopy->save();
            }

            if ($product->serviceMeta) {
                $serviceMetaCopy = $product->serviceMeta->replicate();
                $serviceMetaCopy->product_id = $copy->id;
                $serviceMetaCopy->save();
            }

            $copy->personalizationMockups()->sync(
                $product->personalizationMockups
                    ->mapWithKeys(fn (PersonalizationMockup $mockup) => [
                        $mockup->id => [
                            'sort_order' => (int) ($mockup->pivot?->sort_order ?? 0),
                            'is_default' => (bool) ($mockup->pivot?->is_default ?? false),
                        ],
                    ])
                    ->all()
            );

            if ($product->personalizationTemplate) {
                $templateCopy = $this->duplicateTemplateForProduct($product->personalizationTemplate, $copy);
                PersonalizationTemplateSnapshot::regenerate($templateCopy);
            }

            return $copy;
        });

        return redirect()
            ->route('admin.catalog.products.edit', $duplicate)
            ->with('status', 'Product duplicated as a draft.');
    }

    private function formData(Product $product): array
    {
        $activeTemplateQuery = PersonalizationTemplate::with([
            'product',
            'fields',
            'fonts',
            'mockups.map',
        ])->where(function ($query) use ($product) {
            $query->where(function ($unusedQuery) {
                $unusedQuery->where('is_active', true)
                    ->whereNull('product_id');
            });

            if ($product->personalizationTemplate?->id) {
                $query->orWhere('id', $product->personalizationTemplate->id);
            }
        });

        $availableMockupQuery = PersonalizationMockup::query()
            ->with(['template', 'map'])
            ->where('is_active', true);

        if ($product->exists) {
            $selectedMockupIds = $product->personalizationMockups()->pluck('personalization_mockups.id');

            if ($selectedMockupIds->isNotEmpty()) {
                $availableMockupQuery->orWhereIn('id', $selectedMockupIds);
            }
        }

        return [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'collections' => Collection::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'relatedProducts' => Product::with('variants')->when($product->exists, fn ($query) => $query->whereKeyNot($product->id))->orderBy('name')->get(),
            'relatedCategories' => Category::when($product->exists, fn ($query) => $query->whereKeyNot($product->category_id))->orderBy('name')->get(),
            'personalizationTemplates' => $activeTemplateQuery->orderBy('name')->get(),
            'personalizationMockups' => $availableMockupQuery->orderBy('sort_order')->orderBy('title')->get(),
            'productTypes' => ProductType::options(),
        ];
    }

    private function productPayload(ProductRequest $request): array
    {
        $status = $request->input('save_mode') === 'draft'
            ? 'draft'
            : $request->input('status');

        return [
            ...$request->safe()->except([
                'save_mode',
                'collection_ids',
                'tag_ids',
                'related_product_ids',
                'variants',
                'bundle_items',
                'service_meta',
                'assigned_template_id',
                'allowed_mockup_ids',
                'default_mockup_id',
                'personalization_fields_blueprint',
                'variant_media_links',
                'related_category_ids',
                'featured_image_upload',
                'gallery_uploads',
                'existing_images',
            ]),
            'status' => $status,
            'manage_stock' => $request->boolean('manage_stock'),
            'is_featured' => $request->boolean('is_featured'),
            'proof_notes_enabled' => $request->boolean('proof_notes_enabled'),
            'font_presets_enabled' => $request->boolean('font_presets_enabled'),
            'show_combo_savings_badge' => $request->boolean('show_combo_savings_badge', true),
            'show_related_combos_on_product' => $request->boolean('show_related_combos_on_product', true),
            'show_related_combos_in_cart' => $request->boolean('show_related_combos_in_cart', true),
            'gallery_default_source' => $request->input('gallery_default_source', 'manual_featured_image'),
            'show_flat_preview_first' => $request->boolean('show_flat_preview_first', true),
            'include_mockup_gallery' => $request->boolean('include_mockup_gallery', true),
            'live_preview_enabled' => $request->boolean('live_preview_enabled', true),
            'featured_image_url' => $request->route('product')?->featured_image_url,
            'personalization_fields_blueprint' => $request->filled('personalization_fields_blueprint')
                ? $this->normalizePersonalizationBlueprint(json_decode($request->input('personalization_fields_blueprint'), true) ?: [])
                : null,
            'variant_media_links' => $request->filled('variant_media_links')
                ? json_decode($request->input('variant_media_links'), true)
                : null,
        ];
    }

    private function normalizePersonalizationBlueprint(array $fields): array
    {
        return collect($fields)
            ->map(function (array $field, int $index): array {
                $label = $field['label'] ?? $field['name'] ?? 'Custom field '.($index + 1);

                return [
                    ...$field,
                    'label' => $label,
                    'field_key' => Str::of($label)->snake()->toString() ?: 'custom_field_'.($index + 1),
                    'preset_values' => collect($field['preset_values'] ?? $field['options'] ?? $field['values'] ?? $field['choices'] ?? [])
                        ->map(fn ($value) => is_array($value) ? ($value['value'] ?? $value['label'] ?? '') : $value)
                        ->map(fn ($value) => trim((string) $value))
                        ->filter()
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function uniqueProductSlug(string $baseSlug): string
    {
        $baseSlug = Str::slug($baseSlug) ?: 'product-copy';
        $candidate = $baseSlug;
        $suffix = 2;

        while (Product::where('slug', $candidate)->exists()) {
            $candidate = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function uniqueProductSku(?string $sku): ?string
    {
        if (! filled($sku)) {
            return null;
        }

        $baseSku = Str::limit($sku.'-COPY', 240, '');
        $candidate = $baseSku;
        $suffix = 2;

        while (Product::where('sku', $candidate)->exists()) {
            $candidate = Str::limit($baseSku.'-'.$suffix, 255, '');
            $suffix++;
        }

        return $candidate;
    }

    private function duplicateTemplateForProduct(PersonalizationTemplate $template, Product $product): PersonalizationTemplate
    {
        $copy = PersonalizationTemplate::create([
            ...collect($template->only([
                'base_template_url',
                'preview_image_url',
                'mask_image_url',
                'export_ratio_width',
                'export_ratio_height',
                'preview_rules',
                'render_rules',
                'preview_data_presets',
                'instructions',
                'safe_zone_notes',
                'proof_note_label',
            ]))->all(),
            'product_id' => $product->id,
            'name' => $template->name.' Copy',
            'thumbnail_image_url' => null,
            'is_active' => false,
        ]);

        foreach ($template->fields as $index => $field) {
            $copy->fields()->create([
                ...collect($field->only([
                    'label',
                    'field_key',
                    'placeholder',
                    'help_text',
                    'default_value',
                    'is_required',
                    'max_length',
                    'min_length',
                    'font_size_min',
                    'font_size_max',
                    'line_height',
                    'letter_spacing',
                    'text_align',
                    'text_color',
                    'position_x',
                    'position_y',
                    'width',
                    'height',
                    'rotation',
                    'z_index',
                    'preview_sample_value',
                    'settings',
                ]))->all(),
                'position' => $index,
            ]);
        }

        foreach ($template->fonts as $index => $font) {
            $copy->fonts()->create([
                ...collect($font->only([
                    'name',
                    'internal_name',
                    'css_font_family',
                    'preview_label',
                    'font_family',
                    'font_source_type',
                    'font_source_value',
                    'category',
                    'style_type',
                    'supported_use',
                    'preview_sample_text',
                    'font_weight_default',
                    'font_style_default',
                    'letter_spacing_default',
                    'line_height_default',
                    'text_transform_default',
                    'recommended_for',
                    'sort_order',
                    'is_default',
                    'is_active',
                ]))->all(),
                'position' => $index,
            ]);
        }

        return $copy;
    }

    private function syncProductRelations(Product $product, array $data): void
    {
        $product->collections()->sync($data['collection_ids'] ?? []);
        $product->tags()->sync($data['tag_ids'] ?? []);
        $product->relatedProducts()->sync($data['related_product_ids'] ?? []);
        $product->relatedCategories()->sync($data['related_category_ids'] ?? []);

        $this->syncPersonalizationAssignments($product, $data);

        if (array_key_exists('variants', $data)) {
            $product->variants()->delete();
            collect($data['variants'] ?? [])
                ->filter(fn (array $variant) => filled($variant['name'] ?? null))
                ->values()
                ->each(fn (array $variant, int $index) => $product->variants()->create([
                    'name' => $variant['name'],
                    'sku' => $variant['sku'] ?: null,
                    'option_values' => filled($variant['option_values'] ?? null)
                        ? array_map('trim', explode(',', $variant['option_values']))
                        : [],
                    'price' => $variant['price'] ?? null,
                    'compare_at_price' => $variant['compare_at_price'] ?? null,
                    'stock_quantity' => $variant['stock_quantity'] ?? 0,
                    'is_default' => (bool) ($variant['is_default'] ?? false),
                    'position' => $index,
                ]));
        }

        if (array_key_exists('bundle_items', $data)) {
            $product->bundleItems()->delete();
            collect($data['bundle_items'] ?? [])
                ->filter(fn (array $item) => filled($item['child_product_id'] ?? null))
                ->values()
                ->each(fn (array $item, int $index) => $product->bundleItems()->create([
                    'child_product_id' => $item['child_product_id'],
                    'quantity' => $item['quantity'] ?? 1,
                    'is_required' => (bool) ($item['is_required'] ?? true),
                    'default_variant_id' => $item['default_variant_id'] ?? null,
                    'allowed_variant_ids' => $item['allowed_variant_ids'] ?? [],
                    'variant_change_allowed' => (bool) ($item['variant_change_allowed'] ?? false),
                    'discount_eligible' => (bool) ($item['discount_eligible'] ?? true),
                    'excluded_upgrade' => (bool) ($item['excluded_upgrade'] ?? false),
                    'price_mode' => $item['price_mode'] ?? 'add_child_price',
                    'custom_price' => $item['custom_price'] ?? null,
                    'display_label' => $item['display_label'] ?? null,
                    'show_on_hero' => (bool) ($item['show_on_hero'] ?? true),
                    'show_in_details' => (bool) ($item['show_in_details'] ?? true),
                    'position' => $index,
                ]));
        }

        if (array_key_exists('service_meta', $data)) {
            $serviceMeta = $data['service_meta'] ?? [];

            if ($product->type === ProductType::Service && collect($serviceMeta)->filter()->isNotEmpty()) {
                $product->serviceMeta()->updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'service_type' => $serviceMeta['service_type'] ?? null,
                        'duration_label' => $serviceMeta['duration_label'] ?? null,
                        'location_scope' => $serviceMeta['location_scope'] ?? null,
                        'requires_advance_payment' => (bool) ($serviceMeta['requires_advance_payment'] ?? false),
                        'advance_payment_amount' => $serviceMeta['advance_payment_amount'] ?? null,
                        'booking_notes' => $serviceMeta['booking_notes'] ?? null,
                        'confirmation_note' => $serviceMeta['confirmation_note'] ?? null,
                        'available_areas' => $serviceMeta['available_areas'] ?? null,
                        'available_days' => $serviceMeta['available_days'] ?? null,
                        'time_slot_options' => $serviceMeta['time_slot_options'] ?? null,
                        'minimum_notice_days' => $serviceMeta['minimum_notice_days'] ?? null,
                        'max_bookings_per_day' => $serviceMeta['max_bookings_per_day'] ?? null,
                        'travel_outside_area_allowed' => (bool) ($serviceMeta['travel_outside_area_allowed'] ?? false),
                        'extra_charge_note' => $serviceMeta['extra_charge_note'] ?? null,
                        'include_items' => $this->decodeMetaJson($serviceMeta['include_items'] ?? null),
                        'packages' => $this->decodeMetaJson($serviceMeta['packages'] ?? null),
                        'booking_flow' => $this->decodeMetaJson($serviceMeta['booking_flow'] ?? null),
                        'before_appointment' => $this->decodeMetaJson($serviceMeta['before_appointment'] ?? null),
                        'pricing_notes' => $this->decodeMetaJson($serviceMeta['pricing_notes'] ?? null),
                        'policies' => $this->decodeMetaJson($serviceMeta['policies'] ?? null),
                        'faqs' => $this->decodeMetaJson($serviceMeta['faqs'] ?? null),
                        'gallery_intro_text' => $serviceMeta['gallery_intro_text'] ?? null,
                    ],
                );
            } else {
                $product->serviceMeta()->delete();
            }
        }

        $this->syncProductMedia($product, $data);
    }

    private function decodeMetaJson(?string $value): array
    {
        if (! filled($value)) {
            return [];
        }

        return json_decode($value, true) ?: [];
    }

    private function syncPersonalizationAssignments(Product $product, array $data): void
    {
        if (! $this->supportsNikahPersonalization($product, $data)) {
            if ($product->personalizationTemplate) {
                $product->personalizationTemplate()->update(['product_id' => null]);
            }

            $product->personalizationMockups()->sync([]);

            return;
        }

        $selectedTemplateId = filled($data['assigned_template_id'] ?? null)
            ? (int) $data['assigned_template_id']
            : null;

        if ($selectedTemplateId) {
            PersonalizationTemplate::query()
                ->where('product_id', $product->id)
                ->where('id', '!=', $selectedTemplateId)
                ->update(['product_id' => null]);

            PersonalizationTemplate::query()
                ->where('id', $selectedTemplateId)
                ->update(['product_id' => $product->id]);
        } elseif ($product->personalizationTemplate) {
            $product->personalizationTemplate()->update(['product_id' => null]);
        }

        $allowedMockupIds = collect($data['allowed_mockup_ids'] ?? [])
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $defaultMockupId = filled($data['default_mockup_id'] ?? null)
            ? (int) $data['default_mockup_id']
            : null;

        $syncPayload = $allowedMockupIds
            ->values()
            ->mapWithKeys(fn (int $id, int $index) => [
                $id => [
                    'sort_order' => $index,
                    'is_default' => $defaultMockupId ? $id === $defaultMockupId : $index === 0,
                ],
            ])
            ->all();

        $product->personalizationMockups()->sync($syncPayload);
    }

    private function supportsNikahPersonalization(Product $product, array $data): bool
    {
        $type = $product->type instanceof ProductType
            ? $product->type
            : ProductType::tryFrom((string) $product->type);

        if ($type === ProductType::AdvancedPersonalized) {
            return true;
        }

        $categoryId = $data['category_id'] ?? $product->category_id;

        if (! $categoryId) {
            return false;
        }

        $category = Category::query()->find($categoryId);

        if (! $category) {
            return false;
        }

        $label = strtolower(trim($category->name.' '.$category->slug));

        return str_contains($label, 'nikah');
    }

    private function syncProductMedia(Product $product, array $data): void
    {
        $featuredImageUrl = $product->featured_image_url;
        $shouldRefreshPrimaryImage = false;

        if (request()->hasFile('featured_image_upload')) {
            $featuredImageUrl = $this->storeImage(request()->file('featured_image_upload'));
        }

        if (array_key_exists('existing_images', $data)) {
            $existingImages = collect($data['existing_images'] ?? []);
            $shouldRefreshPrimaryImage = true;

            $product->images->each(function (ProductImage $image) use ($existingImages): void {
                $row = $existingImages->get((string) $image->id, $existingImages->get($image->id, []));

                if ((bool) ($row['remove'] ?? false)) {
                    $image->delete();

                    return;
                }

                $image->update([
                    'label' => $row['label'] ?? $image->label,
                    'alt_text' => $row['alt_text'] ?? $image->alt_text,
                    'position' => $row['position'] ?? $image->position,
                    'is_primary' => (bool) ($row['is_primary'] ?? false),
                    'status' => 'active',
                ]);
            });
        }

        if (request()->hasFile('gallery_uploads')) {
            $basePosition = (int) $product->images()->max('position');
            $shouldRefreshPrimaryImage = true;

            collect(request()->file('gallery_uploads'))
                ->filter(fn (?UploadedFile $file) => $file instanceof UploadedFile)
                ->values()
                ->each(function (UploadedFile $file, int $index) use ($product, $basePosition): void {
                    $product->images()->create([
                        'label' => 'gallery',
                        'image_url' => $this->storeImage($file),
                        'alt_text' => $product->name.' gallery image',
                        'is_primary' => false,
                        'status' => 'active',
                        'position' => $basePosition + $index + 1,
                    ]);
                });
        }

        if ($shouldRefreshPrimaryImage) {
            $images = $product->images()->orderBy('position')->get();
            $primaryImage = $images->firstWhere('is_primary', true) ?? $images->first();

            $product->images()->whereKey($images->pluck('id'))->update(['is_primary' => false]);

            if ($primaryImage) {
                $primaryImage->update(['is_primary' => true]);
                $featuredImageUrl = $featuredImageUrl ?: $primaryImage->image_url;
            }
        }

        $product->update([
            'featured_image_url' => $featuredImageUrl,
        ]);
    }

    private function storeImage(UploadedFile $file): string
    {
        $path = $file->store('products', 'public');

        return Storage::url($path);
    }
}
