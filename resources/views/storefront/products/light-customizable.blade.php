@php
    use Illuminate\Support\Str;

    $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
    $galleryImages = $product->images
        ->take(8)
        ->map(fn ($image) => [
            'id' => $image->id,
            'url' => $image->image_url,
            'thumb' => $image->image_url,
            'alt' => $image->alt_text ?: $image->label ?: $product->name,
            'label' => $image->label ?: $product->name,
        ])
        ->values();
    $featuredGeneralImage = $product->featured_image_url
        ? collect([[
            'id' => 'featured',
            'url' => $product->featured_image_url,
            'thumb' => $product->featured_image_url,
            'alt' => $product->name,
            'label' => 'Featured image',
        ]])
        : collect();
    $generalImages = $featuredGeneralImage
        ->merge($galleryImages->reject(fn ($image) => $product->featured_image_url && $image['url'] === $product->featured_image_url))
        ->values();

    $customFields = collect($product->personalization_fields_blueprint ?? [])
        ->map(function ($field, $index) {
            $label = $field['label'] ?? $field['name'] ?? 'Custom field';
            $key = $field['field_key'] ?? $field['key'] ?? Str::of($label)->slug('_')->toString();
            $rawPresets = $field['preset_values'] ?? $field['options'] ?? $field['values'] ?? $field['choices'] ?? [];

            if (is_string($rawPresets)) {
                $rawPresets = preg_split('/\r\n|\r|\n/', $rawPresets) ?: [];
            }

            return [
                'label' => $label,
                'key' => $key ?: 'custom_field_'.$index,
                'type' => $field['type'] ?? 'text',
                'is_required' => (bool) ($field['is_required'] ?? $field['required'] ?? false),
                'help_text' => $field['help_text'] ?? $field['help'] ?? '',
                'preset_values' => collect($rawPresets)
                    ->map(fn ($value) => is_array($value) ? ($value['value'] ?? $value['label'] ?? '') : $value)
                    ->map(fn ($value) => trim((string) $value))
                    ->filter(fn ($value) => filled($value))
                    ->values(),
            ];
        })
        ->filter(fn ($field) => filled($field['key']))
        ->values();

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
                        'type' => Str::of($key)->contains(['color', 'frame_type', 'material']) ? 'swatch' : 'pill',
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
    $badgeItems = collect(['Light customization', $product->manage_stock ? ($product->stock_quantity.' available') : 'Made to order', 'Fast checkout'])
        ->filter()
        ->values();
    $shortDescription = $product->excerpt ?: Str::limit(strip_tags($product->description), 150);
    $storyVisual = $product->storefront_preview_image_url ?: $product->featured_image_url ?: $primaryImage?->image_url;
    $deliveryRows = [
        ['label' => 'Production time', 'value' => ($product->lead_time_days ?: 4).' to '.(($product->lead_time_days ?: 4) + 2).' business days'],
        ['label' => 'Personalization', 'value' => 'Text details are reviewed before production'],
        ['label' => 'Delivery estimate', 'value' => '2 to 5 business days after dispatch'],
        ['label' => 'Packaging', 'value' => 'Gift-ready wrapped and carefully posted'],
    ];
    $currentProductSummary = [
        'id' => $product->id,
        'name' => $product->name,
        'price' => (float) $product->price,
        'url' => route('products.show', $product),
        'image' => $storyVisual,
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
            'image' => $generalImages->pluck('url')->filter()->values()->all(),
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
    <div
        class="text-[var(--text-main)]"
        x-data="storefrontPdp({
            isCustomizable: false,
            generalImages: @js($generalImages->values()->all()),
            variants: @js($simpleVariants->values()->all()),
            variantMediaLinks: @js($product->variant_media_links ?? []),
            variantGroups: @js($variantGroups->values()->all()),
            selectedVariant: @js($selectedVariant),
            selectedVariants: @js($selectedVariantGroups),
            quantity: @js((int) old('quantity', 1)),
            currentProduct: @js($currentProductSummary),
            basePrice: @js((float) $product->price),
            baseComparePrice: @js($product->compare_at_price ? (float) $product->compare_at_price : null),
        })"
    >
        <div class="mx-auto max-w-screen-xl px-2.5 py-4 sm:px-6 lg:px-8 lg:py-8">
            <nav class="flex flex-wrap items-center gap-1 text-xs text-[var(--text-muted)]">
                <a href="{{ route('home') }}" class="transition duration-200 ease-out hover:text-[var(--accent-primary)] hover:underline">Home</a>
                <span>/</span>
                @if ($product->category)
                    <a href="{{ route('categories.show', $product->category) }}" class="transition duration-200 ease-out hover:text-[var(--accent-primary)] hover:underline">{{ $product->category->name }}</a>
                    <span>/</span>
                @endif
                <span class="text-[var(--text-main)]">{{ $product->name }}</span>
            </nav>

            <div class="mt-4 grid gap-5 lg:mt-6 lg:grid-cols-[minmax(0,55fr)_minmax(0,45fr)] lg:gap-8">
                @include('products.partials._preview_stage', [
                    'product' => $product,
                    'template' => null,
                    'mockups' => collect(),
                    'generalImages' => $generalImages,
                ])

                <section class="space-y-4 text-[var(--text-main)]">
                    <div class="surface-card-featured p-5 sm:p-6">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($badgeItems as $index => $badge)
                                @php
                                    $badgeClasses = match ($index) {
                                        0 => 'bg-[var(--pill-bg)] text-[var(--accent-primary)]',
                                        1 => 'bg-[rgba(120,0,0,0.08)] text-[var(--accent-primary)]',
                                        default => 'bg-[rgba(0,48,73,0.08)] text-[var(--accent-secondary)]',
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-medium {{ $badgeClasses }}">{{ $badge }}</span>
                            @endforeach
                        </div>

                        <h1 class="mt-2 font-serif text-[26px] font-semibold leading-tight text-[var(--text-main)]">{{ $product->name }}</h1>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="text-2xl font-semibold text-[var(--accent-primary)]" x-text="formatMoney(displayPrice)">BDT {{ number_format((float) $product->price, 0) }}</span>
                            @if ($product->compare_at_price)
                                <span class="text-sm text-[var(--text-muted)] line-through" x-show="displayComparePrice" x-text="formatMoney(displayComparePrice)">BDT {{ number_format((float) $product->compare_at_price, 0) }}</span>
                                <span class="rounded-full bg-[rgba(120,0,0,0.08)] px-2 py-0.5 text-xs font-medium text-[var(--accent-primary)]" x-show="savePercent > 0" x-text="`SAVE ${savePercent}%`"></span>
                            @endif
                        </div>

                        <p class="mt-2 text-sm leading-relaxed text-[var(--text-muted)]">{{ $shortDescription }}</p>
                    </div>

                    <form id="order-form" method="POST" action="{{ route('cart.store', $product) }}" class="space-y-4" x-ref="mainOrderForm" @submit="submitting = true">
                        @csrf

                        <div class="surface-card p-5">
                            @include('products.partials._variant_selectors', [
                                'variantGroups' => $variantGroups,
                                'simpleVariants' => $simpleVariants,
                            ])
                        </div>

                        <div class="surface-card p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-base font-semibold text-[var(--text-main)]">Personalize</h2>
                                <span class="text-xs font-medium uppercase tracking-[0.14em] text-[var(--text-muted)]">Light edit</span>
                            </div>

                            @if ($product->personalization_help_text)
                                <p class="mt-2 text-sm leading-7 text-[var(--text-muted)]">{{ $product->personalization_help_text }}</p>
                            @endif

                            <div class="mt-4 space-y-4">
                                @forelse ($customFields as $field)
                                    @php($fieldId = 'personalization_'.$field['key'])
                                    <div class="space-y-2" x-data="{ value: @js(old('personalization.'.$field['key'], '')) }">
                                        <label for="{{ $fieldId }}" class="block text-sm font-semibold text-[var(--text-main)]">
                                            {{ $field['label'] }}
                                            @if ($field['is_required'])
                                                <span class="text-[var(--accent-primary)]">*</span>
                                            @endif
                                        </label>

                                        @if ($field['preset_values']->isNotEmpty())
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($field['preset_values'] as $presetValue)
                                                    <button type="button" class="rounded-full border border-[rgba(0,48,73,0.14)] px-3 py-1.5 text-xs font-medium text-[var(--accent-secondary)] transition hover:border-[var(--accent-secondary)] hover:bg-[rgba(0,48,73,0.06)]" @click="value = @js($presetValue)">
                                                        {{ $presetValue }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($field['type'] === 'textarea')
                                            <textarea
                                                id="{{ $fieldId }}"
                                                name="personalization[{{ $field['key'] }}]"
                                                rows="3"
                                                class="field-textarea"
                                                x-model="value"
                                                @required($field['is_required'])
                                            ></textarea>
                                        @else
                                            <input
                                                id="{{ $fieldId }}"
                                                type="{{ in_array($field['type'], ['date', 'number', 'email', 'tel'], true) ? $field['type'] : 'text' }}"
                                                name="personalization[{{ $field['key'] }}]"
                                                value="{{ old('personalization.'.$field['key']) }}"
                                                class="field-input"
                                                x-model="value"
                                                @required($field['is_required'])
                                            >
                                        @endif

                                        @if ($field['help_text'])
                                            <p class="text-xs text-[var(--text-muted)]">{{ $field['help_text'] }}</p>
                                        @endif

                                        @error('personalization.'.$field['key'])
                                            <p class="text-xs font-medium text-[var(--color-danger)]">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @empty
                                    <label class="block space-y-2">
                                        <span class="text-sm font-semibold text-[var(--text-main)]">Custom text</span>
                                        <input type="text" name="custom_text" value="{{ old('custom_text') }}" maxlength="120" placeholder="Enter a short name or message" class="field-input">
                                        <span class="text-xs text-[var(--text-muted)]">Maximum 120 characters.</span>
                                    </label>
                                @endforelse
                            </div>
                        </div>

                        <div class="surface-card p-5">
                            <h2 class="text-base font-semibold text-[var(--text-main)]">Quantity</h2>
                            <div class="mt-4 inline-flex items-center overflow-hidden rounded-lg border border-[var(--border-soft)]">
                                <button type="button" class="px-4 py-2.5 transition duration-200 ease-out hover:bg-[var(--bg-section-soft)]" @click="quantity = Math.max(1, quantity - 1)" aria-label="Decrease quantity">-</button>
                                <input type="number" min="1" name="quantity" x-model="quantity" class="min-w-[48px] border-0 bg-white px-4 py-2.5 text-center text-sm font-medium text-[var(--text-main)] focus:outline-none focus:ring-0">
                                <button type="button" class="px-4 py-2.5 transition duration-200 ease-out hover:bg-[var(--bg-section-soft)]" @click="quantity = quantity + 1" aria-label="Increase quantity">+</button>
                            </div>
                            @error('quantity')
                                <p class="mt-2 text-[11px] text-[var(--color-danger)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="surface-card-featured p-5" x-ref="ctaAnchor">
                            <button type="submit" class="button-primary relative mt-0 w-full overflow-hidden !rounded-[var(--radius-xl)] !py-4 !text-base">
                                <span x-show="!submitting">Add to cart</span>
                                <span x-cloak x-show="submitting" class="absolute inset-0 flex items-center justify-center bg-[var(--accent-primary)]">
                                    <svg class="h-5 w-5 animate-spin text-white" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle>
                                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" class="opacity-90"></path>
                                    </svg>
                                </span>
                            </button>

                            <button type="submit" name="buy_now" value="1" class="button-ghost mt-2 w-full !rounded-[var(--radius-xl)] !py-3.5 !text-sm !text-[var(--accent-primary)]">
                                Buy it now
                            </button>

                            <div class="mt-4 border-t border-[var(--border-soft)] pt-4">
                                <div class="grid gap-2 text-[11px] text-[var(--text-muted)] sm:grid-cols-3">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 text-[var(--accent-soft)]" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M6.4 11.2 3.2 8l1.1-1.1 2.1 2.1 5-5L12.5 5z"/></svg>
                                        <span>Reviewed details</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 text-[var(--accent-soft)]" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 1a3 3 0 0 0-3 3v2H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1h-1V4a3 3 0 0 0-3-3Zm-1.5 5V4a1.5 1.5 0 0 1 3 0v2h-3Z"/></svg>
                                        <span>Secure checkout</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 text-[var(--accent-soft)]" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2 4.5 8 1l6 3.5V12L8 15l-6-3V4.5Zm2 .7V11l4 2.2 4-2.2V5.2L8 3 4 5.2Z"/></svg>
                                        <span>Careful packing</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </section>
            </div>

            @include('products.partials._below_fold', [
                'product' => $product,
                'storyVisual' => $storyVisual,
                'deliveryRows' => $deliveryRows,
                'faqs' => $faqs ?? collect(),
                'relatedProducts' => $related_products ?? collect(),
                'relatedCategories' => $product->relatedCategories->take(4),
            ])

            @if ($comboUpsells->isNotEmpty())
                <section class="mt-6 space-y-4 lg:col-span-2">
                    <div class="surface-card-featured p-6 sm:p-8">
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--accent-primary)]">Premium combos you may love</p>
                        <h2 class="mt-3 font-serif text-2xl font-semibold text-[var(--text-main)]">Pair this with a curated bridal set</h2>
                        <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--text-muted)]">Explore small add-ons and larger sets that work naturally with this order.</p>
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
                                            <p class="mt-2 text-sm font-semibold text-[var(--accent-primary)]">Combo price BDT {{ number_format($pricing['final_total'], 0) }}</p>
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
                    <button type="button" class="button-primary !rounded-[var(--radius-lg)] !px-5 !py-2.5 !text-sm" @click="$refs.mainOrderForm?.requestSubmit()">
                        Add to cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.product-detail>
