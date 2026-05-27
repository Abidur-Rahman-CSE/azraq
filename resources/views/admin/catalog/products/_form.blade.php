@php
    $isEdit = $product->exists;
    $selectedCollections = collect(old('collection_ids', $product->collections->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
    $selectedTags = collect(old('tag_ids', $product->tags->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
    $selectedRelated = collect(old('related_product_ids', $product->relatedProducts->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
    $selectedRelatedCategories = collect(old('related_category_ids', $product->relatedCategories->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
    $selectedTemplateId = old('assigned_template_id', $product->personalizationTemplate?->id);
    $selectedMockupIds = collect(old('allowed_mockup_ids', $product->personalizationMockups->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();
    $currentType = old('type', $isEdit ? ($product->type?->value ?? $product->type) : 'standard');
    $existingImages = $product->images->sortBy('position')->values();
    $defaultMockupId = old(
        'default_mockup_id',
        optional($product->personalizationMockups->firstWhere('pivot.is_default', true))->id ?? $product->personalizationMockups->first()?->id
    );
    $preferredNikahCategoryId = old('category_id', $product->category_id);

    $decodedBlueprint = old('personalization_fields_blueprint');

    if (is_string($decodedBlueprint) && filled($decodedBlueprint)) {
        $decodedBlueprint = json_decode($decodedBlueprint, true);
    }

    $personalizationFieldsBlueprint = collect(
        $decodedBlueprint
        ?? $product->personalization_fields_blueprint
        ?? []
    )
        ->map(function ($field) {
            return [
                'id' => $field['id'] ?? null,
                'label' => $field['label'] ?? $field['name'] ?? 'Field',
                'field_key' => $field['field_key'] ?? $field['key'] ?? null,
                'type' => $field['type'] ?? data_get($field, 'settings.input_type'),
                'is_required' => (bool) ($field['is_required'] ?? $field['required'] ?? false),
                'help_text' => $field['help_text'] ?? $field['help'] ?? '',
                'preset_values' => collect($field['preset_values'] ?? $field['options'] ?? $field['values'] ?? $field['choices'] ?? [])
                    ->map(fn ($value) => is_array($value) ? ($value['value'] ?? $value['label'] ?? '') : $value)
                    ->filter(fn ($value) => filled($value))
                    ->values()
                    ->all(),
                'allow_custom_value' => (bool) ($field['allow_custom_value'] ?? $field['allow_custom'] ?? true),
            ];
        })
        ->values()
        ->all();

    $variantMediaLinks = old('variant_media_links');

    if (is_string($variantMediaLinks) && filled($variantMediaLinks)) {
        $variantMediaLinks = json_decode($variantMediaLinks, true);
    }

    $variantMediaLinks = $variantMediaLinks
        ?? $product->variant_media_links
        ?? [];

    $variantPayload = collect(old('variants', $product->variants->map(function ($variant) {
        return [
            'id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'option_values' => filled($variant->option_values ?? null)
                ? implode(', ', $variant->option_values)
                : null,
            'price' => $variant->price,
            'compare_at_price' => $variant->compare_at_price,
            'stock_quantity' => $variant->stock_quantity,
            'is_default' => (bool) $variant->is_default,
        ];
    })->all() ?? []))
        ->values()
        ->map(function ($variant) {
            return [
                'id' => $variant['id'] ?? null,
                'name' => $variant['name'] ?? '',
                'sku' => $variant['sku'] ?? '',
                'option_values' => $variant['option_values'] ?? '',
                'price' => isset($variant['price']) ? (string) $variant['price'] : '',
                'compare_at_price' => isset($variant['compare_at_price']) ? (string) $variant['compare_at_price'] : '',
                'stock_quantity' => isset($variant['stock_quantity']) ? (string) $variant['stock_quantity'] : '0',
                'is_default' => (bool) ($variant['is_default'] ?? false),
            ];
        })
        ->all();

    $bundleItemPayload = collect(old('bundle_items', $product->bundleItems->map(function ($item) {
        return [
            'child_product_id' => $item->child_product_id,
            'quantity' => $item->quantity,
            'is_required' => $item->is_required,
            'default_variant_id' => $item->default_variant_id,
            'allowed_variant_ids' => $item->allowed_variant_ids ?? [],
            'variant_change_allowed' => $item->variant_change_allowed,
            'discount_eligible' => $item->discount_eligible,
            'excluded_upgrade' => $item->excluded_upgrade,
            'price_mode' => $item->price_mode,
            'custom_price' => $item->custom_price,
            'display_label' => $item->display_label,
            'show_on_hero' => $item->show_on_hero,
            'show_in_details' => $item->show_in_details,
        ];
    })->all() ?? []))
        ->values()
        ->map(fn ($item) => [
            'child_product_id' => isset($item['child_product_id']) ? (string) $item['child_product_id'] : '',
            'quantity' => isset($item['quantity']) ? (string) $item['quantity'] : '1',
            'is_required' => (bool) ($item['is_required'] ?? true),
            'default_variant_id' => isset($item['default_variant_id']) ? (string) $item['default_variant_id'] : '',
            'allowed_variant_ids' => collect($item['allowed_variant_ids'] ?? [])->map(fn ($id) => (string) $id)->values()->all(),
            'variant_change_allowed' => (bool) ($item['variant_change_allowed'] ?? false),
            'discount_eligible' => (bool) ($item['discount_eligible'] ?? true),
            'excluded_upgrade' => (bool) ($item['excluded_upgrade'] ?? false),
            'price_mode' => $item['price_mode'] ?? 'add_child_price',
            'custom_price' => isset($item['custom_price']) ? (string) $item['custom_price'] : '',
            'display_label' => $item['display_label'] ?? '',
            'show_on_hero' => (bool) ($item['show_on_hero'] ?? true),
            'show_in_details' => (bool) ($item['show_in_details'] ?? true),
        ])
        ->all();

    $serviceMetaPayload = old('service_meta', [
        'service_type' => $product->serviceMeta?->service_type,
        'duration_label' => $product->serviceMeta?->duration_label,
        'location_scope' => $product->serviceMeta?->location_scope,
        'requires_advance_payment' => $product->serviceMeta?->requires_advance_payment ?? false,
        'advance_payment_amount' => $product->serviceMeta?->advance_payment_amount,
        'booking_notes' => $product->serviceMeta?->booking_notes,
        'confirmation_note' => $product->serviceMeta?->confirmation_note,
        'available_areas' => $product->serviceMeta?->available_areas,
        'available_days' => $product->serviceMeta?->available_days,
        'time_slot_options' => $product->serviceMeta?->time_slot_options,
        'minimum_notice_days' => $product->serviceMeta?->minimum_notice_days,
        'max_bookings_per_day' => $product->serviceMeta?->max_bookings_per_day,
        'travel_outside_area_allowed' => $product->serviceMeta?->travel_outside_area_allowed ?? false,
        'extra_charge_note' => $product->serviceMeta?->extra_charge_note,
        'include_items' => $product->serviceMeta?->include_items ?? [],
        'packages' => $product->serviceMeta?->packages ?? [],
        'booking_flow' => $product->serviceMeta?->booking_flow ?? [],
        'before_appointment' => $product->serviceMeta?->before_appointment ?? [],
        'pricing_notes' => $product->serviceMeta?->pricing_notes ?? [],
        'policies' => $product->serviceMeta?->policies ?? [],
        'faqs' => $product->serviceMeta?->faqs ?? [],
        'gallery_intro_text' => $product->serviceMeta?->gallery_intro_text,
    ]);

    $designPayload = $personalizationTemplates->map(function ($template) {
        $snapshotUrl = $template->thumbnail_image_url
            ? $template->thumbnail_image_url.'?v='.urlencode((string) optional($template->updated_at)->timestamp)
            : null;

        return [
            'id' => (int) $template->id,
            'name' => $template->name,
            'thumbnail_url' => $snapshotUrl ?: ($template->preview_image_url ?: $template->base_template_url),
            'rendered_preview_url' => $snapshotUrl ?: ($template->preview_image_url ?: $template->base_template_url),
            'preview_url' => $template->preview_image_url ?: $template->base_template_url,
            'fields' => $template->fields->map(function ($field) {
                return [
                    'id' => (int) $field->id,
                    'label' => $field->label,
                    'field_key' => $field->field_key,
                    'is_required' => (bool) $field->is_required,
                    'settings' => $field->settings ?? [],
                ];
            })->values()->all(),
            'mockups' => $template->mockups->map(function ($mockup) {
                $normalizedMap = $mockup->map ? \App\Support\MockupZoneNormalizer::toImageSpace($mockup, $mockup->map) : null;

                return [
                    'id' => (int) $mockup->id,
                    'title' => $mockup->title,
                    'thumb_image_url' => $mockup->thumb_image_url ?: $mockup->base_image_url,
                    'base_image_url' => $mockup->base_image_url,
                    'overlay_image_url' => $mockup->overlay_image_url,
                    'mask_image_url' => $mockup->mask_image_url,
                    'render_mode' => $mockup->render_mode,
                    'map' => $mockup->map ? [
                        'top_left_x' => (float) ($normalizedMap['top_left_x'] ?? 0.2),
                        'top_left_y' => (float) ($normalizedMap['top_left_y'] ?? 0.18),
                        'top_right_x' => (float) ($normalizedMap['top_right_x'] ?? 0.8),
                        'top_right_y' => (float) ($normalizedMap['top_right_y'] ?? 0.18),
                        'bottom_right_x' => (float) ($normalizedMap['bottom_right_x'] ?? 0.8),
                        'bottom_right_y' => (float) ($normalizedMap['bottom_right_y'] ?? 0.82),
                        'bottom_left_x' => (float) ($normalizedMap['bottom_left_x'] ?? 0.2),
                        'bottom_left_y' => (float) ($normalizedMap['bottom_left_y'] ?? 0.82),
                        'opacity' => (float) ($mockup->map->opacity ?? 0.95),
                        'highlight_strength' => (float) ($mockup->map->highlight_strength ?? 0.12),
                        'manual_rotation' => (float) ($mockup->map->manual_rotation ?? 0),
                    ] : null,
                ];
            })->values()->all(),
        ];
    })->values();

    $mockupPayload = $personalizationMockups->map(function ($mockup) {
        $normalizedMap = $mockup->map ? \App\Support\MockupZoneNormalizer::toImageSpace($mockup, $mockup->map) : null;

        return [
            'id' => (int) $mockup->id,
            'title' => $mockup->title,
            'thumb_image_url' => $mockup->thumb_image_url ?: $mockup->base_image_url,
            'base_image_url' => $mockup->base_image_url,
            'overlay_image_url' => $mockup->overlay_image_url,
            'mask_image_url' => $mockup->mask_image_url,
            'render_mode' => $mockup->render_mode,
            'template_name' => $mockup->template?->name,
            'map' => $mockup->map ? [
                'top_left_x' => (float) ($normalizedMap['top_left_x'] ?? 0.2),
                'top_left_y' => (float) ($normalizedMap['top_left_y'] ?? 0.18),
                'top_right_x' => (float) ($normalizedMap['top_right_x'] ?? 0.8),
                'top_right_y' => (float) ($normalizedMap['top_right_y'] ?? 0.18),
                'bottom_right_x' => (float) ($normalizedMap['bottom_right_x'] ?? 0.8),
                'bottom_right_y' => (float) ($normalizedMap['bottom_right_y'] ?? 0.82),
                'bottom_left_x' => (float) ($normalizedMap['bottom_left_x'] ?? 0.2),
                'bottom_left_y' => (float) ($normalizedMap['bottom_left_y'] ?? 0.82),
                'opacity' => (float) ($mockup->map->opacity ?? 0.95),
                'highlight_strength' => (float) ($mockup->map->highlight_strength ?? 0.12),
                'manual_rotation' => (float) ($mockup->map->manual_rotation ?? 0),
            ] : null,
        ];
    })->values();

    $formPayload = [
        'page' => [
            'isEdit' => $isEdit,
            'advancedHeading' => $isEdit ? 'Edit Advanced customization product' : 'Create Advanced customization product',
            'generalHeading' => $isEdit ? 'Edit General product' : 'Create General product',
            'generalSetupLabel' => 'General product setup',
            'personalizationLabel' => 'Personalization',
            'seoLabel' => 'SEO',
            'relatedProductsLabel' => 'Related products',
            'relatedCategoriesLabel' => 'Related categories',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => route('admin.dashboard')],
                ['label' => 'Catalog'],
                ['label' => 'Products', 'href' => route('admin.catalog.products.index')],
                ['label' => $isEdit ? $product->name : 'Create'],
            ],
        ],
        'product' => [
            'name' => old('name', $product->name),
            'excerpt' => old('excerpt', $product->excerpt),
            'categoryId' => $preferredNikahCategoryId ? (string) $preferredNikahCategoryId : '',
            'tagIds' => $selectedTags,
            'relatedProductIds' => $selectedRelated,
            'relatedCategoryIds' => $selectedRelatedCategories,
            'status' => old('status', $product->status ?: 'draft'),
            'currentType' => $currentType,
            'slug' => old('slug', $product->slug),
            'sku' => old('sku', $product->sku),
            'description' => old('description', $product->description),
            'price' => (string) old('price', $product->price),
            'compareAtPrice' => (string) old('compare_at_price', $product->compare_at_price),
            'leadTimeDays' => (string) old('lead_time_days', $product->lead_time_days ?? 0),
            'manageStock' => (bool) old('manage_stock', $product->manage_stock ?? true),
            'stockQuantity' => (string) old('stock_quantity', $product->stock_quantity ?? 0),
            'lowStockThreshold' => (string) old('low_stock_threshold', $product->low_stock_threshold ?? 0),
            'isFeatured' => (bool) old('is_featured', $product->is_featured ?? false),
            'featuredImageUrl' => old('featured_image_url', $product->featured_image_url),
            'videoUrl' => old('video_url', $product->video_url),
            'personalizationHelpText' => old('personalization_help_text', $product->personalization_help_text),
            'comboDiscountType' => old('combo_discount_type', $product->combo_discount_type ?: 'percent'),
            'comboDiscountValue' => (string) old('combo_discount_value', $product->combo_discount_value),
            'comboRoundingRule' => old('combo_rounding_rule', $product->combo_rounding_rule ?: 'none'),
            'showComboSavingsBadge' => (bool) old('show_combo_savings_badge', $product->show_combo_savings_badge ?? true),
            'comboPromoHeadline' => old('combo_promo_headline', $product->combo_promo_headline),
            'comboPromoSubtitle' => old('combo_promo_subtitle', $product->combo_promo_subtitle),
            'marketingLabel' => old('marketing_label', $product->marketing_label),
            'showRelatedCombosOnProduct' => (bool) old('show_related_combos_on_product', $product->show_related_combos_on_product ?? true),
            'showRelatedCombosInCart' => (bool) old('show_related_combos_in_cart', $product->show_related_combos_in_cart ?? true),
            'metaTitle' => old('meta_title', $product->meta_title),
            'metaDescription' => old('meta_description', $product->meta_description),
            'collectionIds' => $selectedCollections,
            'selectedDesignId' => $selectedTemplateId ? (int) $selectedTemplateId : '',
            'activeMockupIds' => $selectedMockupIds,
            'defaultMockupId' => $defaultMockupId ? (int) $defaultMockupId : '',
            'personalizationFields' => $personalizationFieldsBlueprint,
            'variantMediaLinks' => $variantMediaLinks,
            'variants' => $variantPayload,
            'bundleItems' => $bundleItemPayload,
            'serviceMeta' => [
                'service_type' => $serviceMetaPayload['service_type'] ?? '',
                'duration_label' => $serviceMetaPayload['duration_label'] ?? '',
                'location_scope' => $serviceMetaPayload['location_scope'] ?? '',
                'requires_advance_payment' => (bool) ($serviceMetaPayload['requires_advance_payment'] ?? false),
                'advance_payment_amount' => isset($serviceMetaPayload['advance_payment_amount']) ? (string) $serviceMetaPayload['advance_payment_amount'] : '',
                'booking_notes' => $serviceMetaPayload['booking_notes'] ?? '',
                'confirmation_note' => $serviceMetaPayload['confirmation_note'] ?? '',
                'available_areas' => $serviceMetaPayload['available_areas'] ?? '',
                'available_days' => $serviceMetaPayload['available_days'] ?? '',
                'time_slot_options' => $serviceMetaPayload['time_slot_options'] ?? '',
                'minimum_notice_days' => isset($serviceMetaPayload['minimum_notice_days']) ? (string) $serviceMetaPayload['minimum_notice_days'] : '',
                'max_bookings_per_day' => isset($serviceMetaPayload['max_bookings_per_day']) ? (string) $serviceMetaPayload['max_bookings_per_day'] : '',
                'travel_outside_area_allowed' => (bool) ($serviceMetaPayload['travel_outside_area_allowed'] ?? false),
                'extra_charge_note' => $serviceMetaPayload['extra_charge_note'] ?? '',
                'include_items' => $serviceMetaPayload['include_items'] ?? [],
                'packages' => $serviceMetaPayload['packages'] ?? [],
                'booking_flow' => $serviceMetaPayload['booking_flow'] ?? [],
                'before_appointment' => $serviceMetaPayload['before_appointment'] ?? [],
                'pricing_notes' => $serviceMetaPayload['pricing_notes'] ?? [],
                'policies' => $serviceMetaPayload['policies'] ?? [],
                'faqs' => $serviceMetaPayload['faqs'] ?? [],
                'gallery_intro_text' => $serviceMetaPayload['gallery_intro_text'] ?? '',
            ],
            'isNew' => ! $isEdit,
        ],
        'productTypes' => collect($productTypes)
            ->filter(fn ($type) => $type['value'] !== \App\Enums\ProductType::AdvancedPersonalized->value)
            ->values()
            ->all(),
        'categories' => $categories->map(fn ($category) => [
            'id' => (string) $category->id,
            'name' => $category->name,
        ])->values()->all(),
        'relatedCategories' => $relatedCategories->map(fn ($category) => [
            'id' => (int) $category->id,
            'name' => $category->name,
        ])->values()->all(),
        'tags' => $tags->map(fn ($tag) => [
            'id' => (int) $tag->id,
            'name' => $tag->name,
        ])->values()->all(),
        'collections' => $collections->map(fn ($collection) => [
            'id' => (int) $collection->id,
            'name' => $collection->name,
        ])->values()->all(),
        'relatedProducts' => $relatedProducts->map(fn ($relatedProduct) => [
            'id' => (int) $relatedProduct->id,
            'name' => $relatedProduct->name,
            'variants' => $relatedProduct->variants->map(fn ($variant) => [
                'id' => (int) $variant->id,
                'name' => $variant->name,
                'price' => (float) ($variant->price ?: $relatedProduct->price),
            ])->values()->all(),
        ])->values()->all(),
        'existingImages' => $existingImages->map(fn ($image) => [
            'id' => (int) $image->id,
            'image_url' => $image->image_url,
            'label' => $image->label,
            'alt_text' => $image->alt_text,
            'position' => $image->position,
            'is_primary' => (bool) $image->is_primary,
        ])->values()->all(),
        'designs' => $designPayload->all(),
        'mockups' => $mockupPayload->all(),
        'errors' => $errors->toArray(),
    ];
@endphp

<form
    method="POST"
    action="{{ $isEdit ? route('admin.catalog.products.update', $product) : route('admin.catalog.products.store') }}"
    enctype="multipart/form-data"
    class="space-y-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @foreach ($selectedCollections as $collectionId)
        <input type="hidden" name="collection_ids[]" value="{{ $collectionId }}">
    @endforeach

    <div id="nikah-product-form-root"></div>
    <script type="application/json" id="nikah-product-form-payload">@json($formPayload)</script>

    <noscript>
        <div class="rounded-xl border border-[var(--color-border-soft)] bg-white px-5 py-4 text-sm text-[var(--color-text-soft)]">
            This editor needs JavaScript enabled in the admin workspace.
        </div>
    </noscript>
</form>
