@php
    use Illuminate\Support\Str;

    $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
    $generalImages = $product->images
        ->take(8)
        ->map(fn ($image) => [
            'id' => $image->id,
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
        ->map(fn ($font) => \App\Models\PersonalizationFont::stylesheetUrl(
            $font->font_source_type,
            $font->font_source_value ?: $font->name
        ))
        ->filter()
        ->unique()
        ->values();

    $previewPresets = collect($template?->preview_data_presets ?? []);
    $templateBaseArtworkUrl = $template?->baseArtworkUrl();
    $templatePreviewArtworkUrl = $template?->previewArtworkUrl();
    $templateThumbnailArtworkUrl = $template?->thumbnailArtworkUrl();
    $templateRenderedPreviewUrl = $templateThumbnailArtworkUrl
        ? $templateThumbnailArtworkUrl.'?v='.urlencode((string) optional($template?->updated_at)->timestamp)
        : null;

    $templatePayload = $product->is_customizable && $template
        ? [
            'base_template_url' => $templateBaseArtworkUrl,
            'preview_image_url' => $templatePreviewArtworkUrl,
            'rendered_preview_url' => $templateRenderedPreviewUrl ?: $templatePreviewArtworkUrl ?: $templateBaseArtworkUrl,
            'export_ratio_width' => (int) ($template->export_ratio_width ?: 9),
            'export_ratio_height' => (int) ($template->export_ratio_height ?: 13),
            'editor_canvas_width' => 980,
            'storefront_text_scale' => 1.08,
            'preview_data_presets' => $template->preview_data_presets ?? [],
            'fields' => $template->fields->map(fn ($field) => [
                'name'               => $field->field_key,
                'field_key'          => $field->field_key,
                'label'              => $field->label,
                'placeholder'        => $field->placeholder,
                'default_value'      => $field->default_value,
                'preview_sample_value' => $field->preview_sample_value,
                'position_x'         => (float) $field->position_x,
                'position_y'         => (float) $field->position_y,
                'width'              => (float) $field->width,
                'height'             => (float) $field->height,
                'rotation'           => (float) $field->rotation,
                'text_align'         => $field->text_align,
                'text_color'         => $field->text_color,
                'line_height'        => (float) $field->line_height,
                'letter_spacing'     => (float) $field->letter_spacing,
                'font_size_min'      => (int) $field->font_size_min,
                'font_size_max'      => (int) $field->font_size_max,
                'z_index'            => (int) ($field->z_index ?? 1),
                'field_type'         => data_get($field->settings, 'field_type', 'text'),
                'prefix'             => data_get($field->settings, 'prefix', ''),
                'postfix'            => data_get($field->settings, 'postfix', ''),
                'settings'           => $field->settings ?? [],
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

    $proofNoteDefault = old('proof_note', '');

    $badgeItems = $product->is_customizable
        ? collect(['Made to order', 'Proof included', 'Premium finish'])
        : collect(data_get($product, 'badges', []))->filter()->values();

    $shortDescription = $product->excerpt
        ?: Str::limit(strip_tags($product->description), 150);

    $storyVisual = data_get($product, 'story_image')
        ?: $product->storefront_preview_image_url
        ?: $product->featured_image_url
        ?: ($product->is_customizable ? ($templatePreviewArtworkUrl ?: $templateBaseArtworkUrl) : ($primaryImage?->image_url));

    $deliveryRows = [
        ['label' => 'Production time', 'value' => ($product->lead_time_days ?: 4).' to '.(($product->lead_time_days ?: 4) + 2).' business days'],
        ['label' => 'Dispatch', 'value' => $product->is_customizable ? 'After proof approval' : 'Packed after order confirmation'],
        ['label' => 'Delivery estimate', 'value' => '2 to 5 business days after dispatch'],
        ['label' => 'Packaging', 'value' => 'Gift-ready wrapped and carefully posted'],
    ];

    $simpleVariants = $product->variants
        ->map(fn ($variant) => [
            'id' => $variant->id,
            'name' => $variant->name,
            'label' => $variant->name,
            'value' => $variant->name,
            'price' => (float) ($variant->price ?: $product->price),
            'compare_at_price' => $variant->compare_at_price ? (float) $variant->compare_at_price : null,
            'stock_quantity' => (int) $variant->stock_quantity,
            'available' => ! $product->manage_stock || (int) $variant->stock_quantity > 0,
            'option_values' => $variant->option_values ?? [],
            'is_default' => (bool) $variant->is_default,
        ])
        ->values();

    $configuredVariantGroups = collect(data_get($product, 'variantOptions', []))
        ->map(function ($group, $index) {
            $values = collect(data_get($group, 'values', []))
                ->map(fn ($value) => [
                    'label' => data_get($value, 'label', data_get($value, 'value', 'Option')),
                    'value' => data_get($value, 'value', data_get($value, 'label', 'option-'.$index)),
                    'variant_id' => data_get($value, 'variant_id'),
                    'available' => (bool) data_get($value, 'available', true),
                    'tooltip' => data_get($value, 'tooltip'),
                    'swatch' => data_get($value, 'swatch'),
                ])
                ->values();

            return [
                'key' => data_get($group, 'key', 'group_'.$index),
                'name' => data_get($group, 'name', 'Option '.($index + 1)),
                'type' => data_get($group, 'type', 'pill'),
                'values' => $values,
            ];
        })
        ->filter(fn ($group) => $group['values']->isNotEmpty())
        ->values();

    $derivedVariantGroups = $simpleVariants
        ->reduce(function (\Illuminate\Support\Collection $groups, array $variant) {
            foreach (($variant['option_values'] ?? []) as $entry) {
                if (! is_string($entry) || ! str_contains($entry, ':')) {
                    continue;
                }

                [$rawKey, $rawValue] = array_pad(explode(':', $entry, 2), 2, null);
                $key = Str::of((string) $rawKey)->trim()->replace(' ', '_')->lower()->toString();
                $value = trim((string) $rawValue);

                if ($key === '' || $value === '') {
                    continue;
                }

                if (! $groups->has($key)) {
                    $groups->put($key, [
                        'key' => $key,
                        'name' => Str::headline(str_replace('_', ' ', $key)),
                        'type' => str($key)->contains(['color', 'frame_type', 'material']) ? 'swatch' : 'pill',
                        'values' => collect(),
                    ]);
                }

                $group = $groups->get($key);

                if (! $group['values']->contains(fn ($groupValue) => ($groupValue['value'] ?? null) === $value)) {
                    $group['values']->push([
                        'label' => $value,
                        'value' => $value,
                        'variant_id' => null,
                        'available' => true,
                        'tooltip' => null,
                        'swatch' => $value,
                    ]);
                }

                $groups->put($key, $group);
            }

            return $groups;
        }, collect())
        ->map(fn (array $group) => [
            ...$group,
            'values' => $group['values']->values(),
        ])
        ->values();

    $variantGroups = $configuredVariantGroups->isNotEmpty()
        ? $configuredVariantGroups
        : $derivedVariantGroups;

    $selectedVariant = (string) old('variant_id', $product->variants->firstWhere('is_default', true)?->id);
    $selectedVariantGroups = collect(old('selected_variants', []))
        ->mapWithKeys(fn ($value, $key) => [$key => (string) $value])
        ->all();

    $variantsPayload = $simpleVariants->values()->all();
    $currentProductSummary = [
        'id' => $product->id,
        'name' => $product->name,
        'price' => (float) $product->price,
        'url' => route('products.show', $product),
        'image' => $product->storefront_preview_image_url ?: $storyVisual,
    ];
    $comboUpsells = ($comboUpsells ?? collect())->values();
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
        class="text-[var(--text-main)]"
        x-data="storefrontPdp({
            isCustomizable: @js($product->is_customizable),
            canvasId: 'nikah-preview-canvas',
            template: @js($templatePayload),
            fonts: @js($fontsPayload->values()->all()),
            mockups: @js(($mockups instanceof \Illuminate\Support\Collection ? $mockups : collect($mockups ?? []))->values()->all()),
            defaultMockupId: @js($defaultMockupId ?? null),
            showFlatPreviewFirst: true,
            galleryDefaultSource: @js($product->gallery_default_source),
            fields: @js($fieldDefaults),
            fieldFonts: @js($fieldFontDefaults),
            activeFont: @js($defaultFont['key'] ?? null),
            generalImages: @js($generalImages->values()->all()),
            variants: @js($variantsPayload),
            variantMediaLinks: @js($product->variant_media_links ?? []),
            variantGroups: @js($variantGroups->values()->all()),
            selectedVariant: @js($selectedVariant),
            selectedVariants: @js($selectedVariantGroups),
            quantity: @js((int) old('quantity', 1)),
            proofNote: @js($proofNoteDefault),
            currentProduct: @js($currentProductSummary),
            basePrice: @js((float) $product->price),
            baseComparePrice: @js($product->compare_at_price ? (float) $product->compare_at_price : null),
        })"
    >
        <div class="mx-auto max-w-screen-xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <nav class="flex flex-wrap items-center gap-1 text-xs text-[var(--text-muted)]">
                <a href="{{ route('home') }}" class="transition duration-200 ease-out hover:text-[var(--accent-primary)] hover:underline">Home</a>
                <span>/</span>
                @if ($product->category)
                    <a href="{{ route('categories.show', $product->category) }}" class="transition duration-200 ease-out hover:text-[var(--accent-primary)] hover:underline">{{ $product->category->name }}</a>
                    <span>/</span>
                @endif
                <span class="text-[var(--text-main)]">{{ $product->name }}</span>
            </nav>

            <div class="mt-6 grid gap-8 lg:grid-cols-[minmax(0,55fr)_minmax(0,45fr)]">
                @include('products.partials._preview_stage', [
                    'product' => $product,
                    'template' => $template,
                    'mockups' => $mockups,
                    'generalImages' => $generalImages,
                ])

                @if ($product->is_customizable)
                    @include('products.partials._config_panel', [
                        'product' => $product,
                        'template' => $template,
                        'fonts' => $activeFonts,
                        'mockups' => $mockups,
                        'badgeItems' => $badgeItems,
                        'shortDescription' => $shortDescription,
                        'variantGroups' => $variantGroups,
                        'simpleVariants' => $simpleVariants,
                    ])
                @else
                    @include('products.partials._general_panel', [
                        'product' => $product,
                        'badgeItems' => $badgeItems,
                        'shortDescription' => $shortDescription,
                        'variantGroups' => $variantGroups,
                        'simpleVariants' => $simpleVariants,
                    ])
                @endif
            </div>

            @include('products.partials._below_fold', [
                'product' => $product,
                'storyVisual' => $storyVisual,
                'deliveryRows' => $deliveryRows,
                'faqs' => $faqs,
                'relatedProducts' => $related_products,
                'relatedCategories' => $product->relatedCategories->take(4),
            ])

            @if ($comboUpsells->isNotEmpty())
                <section class="mt-6 space-y-4 lg:col-span-2">
                    <div class="surface-card-featured p-6 sm:p-8">
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--accent-primary)]">{{ $product->includedInBundles()->exists() ? 'Complete the set and save more' : 'Premium combos you may love' }}</p>
                        <h2 class="mt-3 font-serif text-2xl font-semibold text-[var(--text-main)]">{{ $product->includedInBundles()->exists() ? 'This item is part of a curated bridal combo' : 'Save more with curated bridal combos' }}</h2>
                        <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--text-muted)]">Explore curated bridal sets designed to make your order feel complete while helping you save more.</p>
                        <div class="mt-6 grid gap-4 lg:grid-cols-3">
                            @foreach ($comboUpsells as $combo)
                                @php($pricing = \App\Support\ComboPricing::summary($combo))
                                <a href="{{ route('products.show', $combo) }}" class="group rounded-xl border border-[var(--border-soft)] bg-white/85 p-4 transition hover:border-[var(--accent-primary)]">
                                    <div class="flex gap-4">
                                        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-lg bg-[var(--bg-section-soft)]">
                                            @if ($combo->storefront_preview_image_url)
                                                <img src="{{ $combo->storefront_preview_image_url }}" alt="{{ $combo->name }}" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--accent-primary)]">{{ $combo->marketing_label ?: 'Combo value' }}</p>
                                            <h3 class="mt-1 line-clamp-2 text-sm font-semibold text-[var(--text-main)]">{{ $combo->name }}</h3>
                                            <p class="mt-2 text-xs text-[var(--text-muted)]">{{ $combo->bundleItems->sum('quantity') }} included pieces</p>
                                            @if ($combo->show_combo_savings_badge ?? true)
                                                <p class="mt-2 text-sm font-semibold text-[var(--accent-primary)]">Save BDT {{ number_format($pricing['savings_amount'], 0) }}</p>
                                            @else
                                                <p class="mt-2 text-sm font-semibold text-[var(--accent-primary)]">Combo price BDT {{ number_format($pricing['final_total'], 0) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="mt-4 inline-flex text-sm font-semibold text-[var(--accent-primary)] group-hover:underline">View combo</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            <div
                class="surface-card sticky bottom-0 z-30 mt-6 border-t px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] backdrop-blur lg:hidden"
                x-show="showStickyBar"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-full opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-full opacity-0"
            >
                <div class="flex items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-[var(--text-main)]">{{ $product->name }}</p>
                        <p class="text-sm font-semibold text-[var(--accent-primary)]" x-text="formatMoney(displayPrice)">BDT {{ number_format((float) $product->price, 0) }}</p>
                    </div>
                    <button
                        type="button"
                        class="button-primary !rounded-[var(--radius-lg)] !px-5 !py-2.5 !text-sm"
                        @click="$refs.mainOrderForm?.requestSubmit()"
                    >
                        {{ $product->is_customizable ? 'Add to cart' : 'Add to cart' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.product-detail>
