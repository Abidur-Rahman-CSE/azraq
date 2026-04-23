@php
    $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
    $generalImages = $product->images
        ->take(8)
        ->map(fn ($image) => [
            'url' => $image->image_url,
            'thumb' => $image->image_url,
            'alt' => $image->alt_text ?: $image->label ?: $product->name,
            'label' => $image->label ?: $product->name,
        ])
        ->values();

    $activeFonts = $fonts instanceof \Illuminate\Support\Collection ? $fonts : collect($fonts ?? []);

    if ($product->is_customizable && $activeFonts->isEmpty() && $template) {
        $activeFonts = $template->fonts
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->values();
    }

    $fontStylesheetUrls = $activeFonts
        ->pluck('font_source_value')
        ->filter()
        ->unique()
        ->values();

    $previewPresets = collect($template?->preview_data_presets ?? []);

    $templatePayload = $product->is_customizable && $template
        ? [
            'base_template_url' => $template->base_template_url,
            'preview_image_url' => $template->preview_image_url ?: $template->base_template_url,
            'rendered_preview_url' => $template->thumbnail_image_url
                ? $template->thumbnail_image_url.'?v='.urlencode((string) optional($template->updated_at)->timestamp)
                : ($template->preview_image_url ?: $template->base_template_url),
            'export_ratio_width' => (int) ($template->export_ratio_width ?: 9),
            'export_ratio_height' => (int) ($template->export_ratio_height ?: 13),
            'editor_canvas_width' => 980,
            'storefront_text_scale' => 1,
            'preview_data_presets' => $template->preview_data_presets ?? [],
            'fields' => $template->fields->map(fn ($field) => [
                'name' => $field->field_key,
                'field_key' => $field->field_key,
                'label' => $field->label,
                'placeholder' => $field->placeholder,
                'default_value' => $field->default_value,
                'preview_sample_value' => $field->preview_sample_value,
                'position_x' => (float) $field->position_x,
                'position_y' => (float) $field->position_y,
                'width' => (float) $field->width,
                'height' => (float) $field->height,
                'rotation' => (float) $field->rotation,
                'text_align' => $field->text_align,
                'text_color' => $field->text_color,
                'line_height' => (float) $field->line_height,
                'letter_spacing' => (float) $field->letter_spacing,
                'font_size_min' => (int) $field->font_size_min,
                'font_size_max' => (int) $field->font_size_max,
                'z_index' => (int) ($field->z_index ?? 1),
                'settings' => $field->settings ?? [],
            ])->values()->all(),
        ]
        : null;

    $fontsPayload = $activeFonts
        ->map(fn ($font) => [
            'key' => (string) $font->id,
            'id' => $font->id,
            'label' => $font->preview_label ?: $font->name,
            'preview_text' => $font->preview_sample_text ?: ($font->preview_label ?: $font->name),
            'css_family' => $font->font_family ?: $font->css_font_family,
            'font_weight' => $font->font_weight_default,
            'font_style' => $font->font_style_default,
            'category' => $font->category,
            'recommended_for' => $font->recommended_for,
            'letter_spacing' => $font->letter_spacing_default,
            'line_height' => $font->line_height_default,
            'text_transform' => $font->text_transform_default,
        ])
        ->values();

    $defaultFont = $fontsPayload->firstWhere('key', (string) old('font_id'))
        ?: $fontsPayload->first();

    $fieldFontDefaults = $product->is_customizable && $template
        ? $template->fields
            ->filter(fn ($field) => str($field->field_key)->contains(['bride', 'groom']))
            ->mapWithKeys(fn ($field) => [
                $field->field_key => (string) old('font_selection.'.$field->field_key, ''),
            ])->all()
        : [];

    $fieldDefaults = $product->is_customizable && $template
        ? $template->fields->mapWithKeys(fn ($field) => [
            $field->field_key => old(
                'personalization.'.$field->field_key,
                $field->default_value
                    ?? $field->preview_sample_value
                    ?? $previewPresets->get($field->field_key, '')
            ),
        ])->all()
        : [];

    $badgeItems = $product->is_customizable
        ? collect(['Made to order', 'Proof included', 'Premium finish'])
        : collect(data_get($product, 'badges', [
            $product->category?->name,
            $product->manage_stock ? ($product->stock_quantity > 0 ? 'Ready to ship' : 'Out of stock') : 'Made to order',
            $product->compare_at_price ? 'Limited offer' : 'Premium finish',
        ]))->filter()->take(3)->values();

    $shortDescription = $product->excerpt
        ?: \Illuminate\Support\Str::limit(strip_tags($product->description), 150);

    $storyVisual = $product->is_customizable
        ? ($template?->preview_image_url ?: $template?->base_template_url)
        : ($primaryImage?->image_url);

    $showFlatPreview = $product->is_customizable
        ? (bool) ($showFlatPreviewFirst ?? false)
        : false;

    $deliveryRows = [
        ['label' => 'Production time', 'value' => ($product->lead_time_days ?: 4).' to '.(($product->lead_time_days ?: 4) + 2).' business days'],
        ['label' => 'Dispatch', 'value' => $product->is_customizable ? 'After proof approval' : 'Packed after order confirmation'],
        ['label' => 'Delivery estimate', 'value' => '2 to 5 business days after dispatch'],
        ['label' => 'Packaging', 'value' => 'Carefully wrapped for gifting and posting'],
    ];
@endphp

<x-layouts.product-detail
    :title="$product->name.' | '.config('brand.name')"
    :description="$product->meta_description ?: ($product->excerpt ?: strip_tags($product->description))"
    :social-image="$storyVisual"
    :schema-data="[
        [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->meta_description ?: ($product->excerpt ?: strip_tags($product->description)),
            'image' => ($product->is_customizable ? collect([$storyVisual])->filter() : $generalImages->pluck('url'))->values()->all(),
            'sku' => $product->sku,
            'category' => $product->category?->name,
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'BDT',
                'price' => (float) $product->price,
                'availability' => $product->manage_stock && $product->stock_quantity <= 0
                    ? 'https://schema.org/OutOfStock'
                    : 'https://schema.org/InStock',
                'url' => route('products.show', $product),
            ],
        ],
    ]"
>
    <x-slot:head>
        @foreach ($fontStylesheetUrls as $fontStylesheetUrl)
            <link rel="stylesheet" href="{{ $fontStylesheetUrl }}">
        @endforeach
    </x-slot:head>

    <div
        class="contents"
        x-data="storefrontPdp({
            isCustomizable: @js($product->is_customizable),
            canvasId: 'nikah-preview-canvas',
            template: @js($templatePayload),
            fonts: @js($fontsPayload->values()->all()),
            mockups: @js(($mockups instanceof \Illuminate\Support\Collection ? $mockups : collect($mockups ?? []))->values()->all()),
            defaultMockupId: @js($defaultMockupId ?? null),
            showFlatPreviewFirst: @js($showFlatPreview),
            galleryDefaultSource: @js($product->gallery_default_source),
            fields: @js($fieldDefaults),
            fieldFonts: @js($fieldFontDefaults),
            activeFont: @js($defaultFont['key'] ?? null),
            generalImages: @js($generalImages->values()->all()),
            selectedVariant: @js((string) old('variant_id', $product->variants->firstWhere('is_default', true)?->id)),
            quantity: @js((int) old('quantity', 1)),
        })"
    >
        @include('products.partials._preview_stage', [
            'product' => $product,
            'template' => $template,
            'mockups' => $mockups,
            'generalImages' => $generalImages,
            'recentlyViewed' => $recentlyViewed,
            'showFlatPreview' => $showFlatPreview,
        ])

        @if ($product->is_customizable)
            @include('products.partials._config_panel', [
                'product' => $product,
                'template' => $template,
                'fonts' => $activeFonts,
                'mockups' => $mockups,
                'badgeItems' => $badgeItems,
                'shortDescription' => $shortDescription,
            ])
        @else
            @include('products.partials._general_panel', [
                'product' => $product,
                'badgeItems' => $badgeItems,
                'shortDescription' => $shortDescription,
            ])
        @endif

        @include('products.partials._below_fold', [
            'product' => $product,
            'storyVisual' => $storyVisual,
            'deliveryRows' => $deliveryRows,
            'faqs' => $faqs,
            'relatedProducts' => $related_products,
            'recentlyViewed' => $recentlyViewed,
        ])

        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-[#E8E3DC] bg-[rgba(250,250,248,0.96)] px-4 py-3 shadow-[0_-2px_16px_rgba(0,0,0,0.06)] backdrop-blur lg:hidden" x-show="showStickyBar" x-transition.opacity.duration.200ms>
            <div class="mx-auto flex max-w-3xl items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-[#2C2C3E]">{{ $product->name }}</p>
                    <p class="text-sm font-semibold text-[#8B2635]">BDT {{ number_format((float) $product->price, 0) }}</p>
                </div>
                <button
                    type="button"
                    class="rounded-xl bg-[#8B2635] px-4 py-3 text-sm font-semibold text-white shadow-[0_10px_24px_rgba(139,38,53,0.18)] transition duration-200 ease-out hover:bg-[#6D1D29]"
                    @click="$refs.mainProductForm?.requestSubmit()"
                >
                    {{ $product->is_customizable ? 'Add order' : 'Add to cart' }}
                </button>
            </div>
        </div>
    </div>
</x-layouts.product-detail>
