@php
    $galleryItems = collect([
        [
            'src' => $template->preview_image_url,
            'label' => $template->name,
            'kind' => 'Live preview',
            'is_template' => true,
        ],
        ...$product->images
            ->filter(fn ($image) => filled($image->image_url) && $image->image_url !== $template->preview_image_url)
            ->map(fn ($image) => [
                'src' => $image->image_url,
                'label' => $image->label ?: $product->name,
                'kind' => 'Lifestyle view',
                'is_template' => false,
            ])->values()->all(),
    ])->values();
@endphp

<x-layouts.product-detail
    :title="$product->name.' | '.config('brand.name')"
    :description="$product->meta_description ?: ($product->excerpt ?: $product->description)"
    :social-image="$template->preview_image_url"
    :schema-data="[
        [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->meta_description ?: ($product->excerpt ?: $product->description),
            'image' => $galleryItems->pluck('src')->all(),
            'sku' => $product->sku,
            'category' => $product->category?->name,
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'BDT',
                'price' => (float) $product->price,
                'availability' => 'https://schema.org/InStock',
                'url' => route('products.show', $product),
            ],
        ],
    ]"
>
    <div
        class="space-y-6"
        x-data="{
            gallery: @js($galleryItems->all()),
            activeIndex: 0,
            showLightbox: false,
            selectedFont: @js(old('font_id', $template->fonts->firstWhere('is_default', true)?->id ?? $template->fonts->first()?->id)),
            fields: @js($template->fields->mapWithKeys(fn ($field) => [$field->field_key => old('personalization.'.$field->field_key, $field->default_value ?? '')])->all()),
            fonts: @js($template->fonts->map(fn ($font) => ['id' => $font->id, 'css_font_family' => $font->css_font_family])->values()),
            activeImage() {
                return this.gallery[this.activeIndex] ?? this.gallery[0];
            },
            fontFamily(id) {
                return this.fonts.find((item) => item.id == id)?.css_font_family ?? 'Poppins, sans-serif';
            },
            setActive(index) {
                this.activeIndex = index;
            },
            nextImage() {
                this.activeIndex = (this.activeIndex + 1) % this.gallery.length;
            },
            previousImage() {
                this.activeIndex = (this.activeIndex - 1 + this.gallery.length) % this.gallery.length;
            }
        }"
        x-on:keydown.right.window="if (showLightbox) nextImage()"
        x-on:keydown.left.window="if (showLightbox) previousImage()"
        x-on:keydown.escape.window="showLightbox = false"
    >
        <div class="surface-configurator overflow-hidden p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <x-storefront.product-breadcrumbs :product="$product" />
                <span class="rounded-full bg-white/80 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-primary-900)]">
                    Signature configurator
                </span>
            </div>

            <div class="mt-6 rounded-[var(--radius-3xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)] p-4 lg:p-5">
                <button
                    type="button"
                    class="relative mx-auto block aspect-[4/5] w-full max-w-[360px] overflow-hidden rounded-[var(--radius-2xl)] border border-white/70 bg-white shadow-[0_20px_50px_rgba(15,46,60,0.12)]"
                    x-on:click="showLightbox = true"
                >
                    <img :src="activeImage().src" :alt="activeImage().label" class="h-full w-full object-cover" fetchpriority="high" decoding="async">

                    @foreach ($template->fields as $field)
                        <div
                            x-show="activeImage().is_template"
                            class="absolute px-2 text-center text-[var(--color-primary-900)]"
                            style="
                                left: {{ $field->position_x }}%;
                                top: {{ $field->position_y }}%;
                                width: {{ $field->width }}%;
                                transform: translate(-50%, -50%);
                                text-align: {{ $field->text_align === 'start' ? 'left' : ($field->text_align === 'end' ? 'right' : 'center') }};
                                color: {{ $field->text_color }};
                                min-height: {{ $field->height }}%;
                                line-height: {{ $field->line_height }};
                                letter-spacing: {{ $field->letter_spacing }}px;
                                font-size: clamp({{ $field->font_size_min }}px, 1.8vw, {{ $field->font_size_max }}px);
                            "
                            :style="`font-family: ${fontFamily(selectedFont)}`"
                            x-text="fields['{{ $field->field_key }}'] || '{{ $field->placeholder }}'"
                        ></div>
                    @endforeach
                </button>

                @if ($galleryItems->count() > 1)
                    <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-5">
                        @foreach ($galleryItems as $index => $galleryItem)
                            <button
                                type="button"
                                class="overflow-hidden rounded-[var(--radius-xl)] border bg-white transition"
                                :class="activeIndex === {{ $index }} ? 'border-[var(--color-primary-900)] shadow-[0_12px_24px_rgba(120,0,0,0.12)]' : 'border-[var(--color-border-soft)]'"
                                x-on:click="setActive({{ $index }})"
                            >
                                <img src="{{ $galleryItem['src'] }}" alt="{{ $galleryItem['label'] }}" class="h-20 w-full object-cover" loading="lazy" decoding="async">
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-[var(--color-text-soft)]">
                    <p x-text="activeImage().kind"></p>
                    <p>Click image to expand</p>
                </div>
            </div>

            <div class="mt-5 rounded-[var(--radius-xl)] bg-white/80 p-5 text-sm leading-7 text-[var(--color-text-soft)]">
                Preview shown for guidance only. Final proof may receive minor alignment polish, and the selected text zones are reserved for certificate-safe composition.
            </div>

            <div
                x-show="showLightbox"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(8,22,31,0.82)] p-6 backdrop-blur-sm"
            >
                <div class="relative w-full max-w-4xl">
                    <button type="button" class="header-icon-button absolute right-0 top-0 z-10 -translate-y-14" x-on:click="showLightbox = false" aria-label="Close preview">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>

                    <div class="relative overflow-hidden rounded-[var(--radius-3xl)] bg-white p-4 shadow-[0_35px_90px_rgba(0,0,0,0.28)]">
                        <div class="relative mx-auto aspect-[4/5] max-h-[80vh] overflow-hidden rounded-[var(--radius-2xl)] bg-[var(--color-surface-cream)]">
                            <img :src="activeImage().src" :alt="activeImage().label" class="h-full w-full object-contain">

                            @foreach ($template->fields as $field)
                                <div
                                    x-show="activeImage().is_template"
                                    class="absolute px-2 text-center text-[var(--color-primary-900)]"
                                    style="
                                        left: {{ $field->position_x }}%;
                                        top: {{ $field->position_y }}%;
                                        width: {{ $field->width }}%;
                                        transform: translate(-50%, -50%);
                                        text-align: {{ $field->text_align === 'start' ? 'left' : ($field->text_align === 'end' ? 'right' : 'center') }};
                                        color: {{ $field->text_color }};
                                        min-height: {{ $field->height }}%;
                                        line-height: {{ $field->line_height }};
                                        letter-spacing: {{ $field->letter_spacing }}px;
                                        font-size: clamp({{ $field->font_size_min }}px, 1.4vw, {{ $field->font_size_max }}px);
                                    "
                                    :style="`font-family: ${fontFamily(selectedFont)}`"
                                    x-text="fields['{{ $field->field_key }}'] || '{{ $field->placeholder }}'"
                                ></div>
                            @endforeach
                        </div>

                        <button type="button" class="header-icon-button absolute left-5 top-1/2 -translate-y-1/2" x-on:click="previousImage()" aria-label="Previous image">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </button>
                        <button type="button" class="header-icon-button absolute right-5 top-1/2 -translate-y-1/2" x-on:click="nextImage()" aria-label="Next image">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                                <path d="M9 6l6 6-6 6" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="surface-sidebar p-8">
            <div class="flex flex-wrap items-center gap-3">
                <span class="eyebrow">Advanced Personalized</span>
                @if ($product->category)
                    <x-storefront.trust-badge :label="$product->category->name" />
                @endif
                <x-storefront.trust-badge label="Made to order" />
                <x-storefront.trust-badge label="Proof-ready workflow" />
            </div>

            <h1 class="mt-5 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">{{ $product->name }}</h1>
            <div class="mt-6">
                <x-storefront.price-block :product="$product" />
            </div>
            <p class="mt-4 text-base leading-8 text-[var(--color-text-soft)]">{{ $product->description }}</p>

            <div class="mt-6 flex flex-wrap gap-3">
                <span class="info-pill">Structured field inputs</span>
                <span class="info-pill">Curated font direction</span>
                <span class="info-pill">Designer proof notes</span>
            </div>

            <form method="POST" action="{{ route('cart.store', $product) }}" class="mt-8 space-y-6">
                @csrf

                <div class="surface-configurator p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-[var(--color-secondary-900)]">Structured personalization</h2>
                        <span class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Template safe zones</span>
                    </div>
                    <div class="mt-5 grid gap-4">
                        @foreach ($template->fields as $field)
                            <label class="field-shell">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $field->label }}</span>
                                    <span class="text-xs text-[var(--color-text-soft)]">Max {{ $field->max_length }}</span>
                                </div>
                                <input
                                    type="text"
                                    name="personalization[{{ $field->field_key }}]"
                                    maxlength="{{ $field->max_length }}"
                                    value="{{ old('personalization.'.$field->field_key, $field->default_value) }}"
                                    placeholder="{{ $field->placeholder }}"
                                    x-model="fields['{{ $field->field_key }}']"
                                    class="field-input"
                                >
                                @if ($field->help_text)
                                    <span class="text-xs text-[var(--color-text-soft)]">{{ $field->help_text }}</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="surface-configurator p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-[var(--color-secondary-900)]">Choose a font</h2>
                        <span class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Curated selection</span>
                    </div>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($template->fonts as $font)
                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    name="font_id"
                                    value="{{ $font->id }}"
                                    class="peer sr-only"
                                    x-model="selectedFont"
                                    @checked(old('font_id', $template->fonts->firstWhere('is_default', true)?->id ?? $template->fonts->first()?->id) == $font->id)
                                >
                                <span class="flex min-h-28 flex-col justify-between rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white px-4 py-4 text-sm transition peer-checked:border-[var(--color-primary-900)] peer-checked:bg-[var(--color-surface-cream)] peer-checked:shadow-[0_18px_35px_rgba(120,0,0,0.12)]">
                                    <span class="font-medium text-[var(--color-secondary-900)]">{{ $font->name }}</span>
                                    <span style="font-family: {{ $font->css_font_family }};" class="mt-4 text-lg text-[var(--color-primary-900)]">{{ $font->preview_label ?: $font->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="surface-configurator p-5">
                    <label class="field-shell">
                        <span class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $template->proof_note_label ?: 'Proof notes' }}</span>
                        <textarea name="proof_note" rows="4" placeholder="Mention any spelling notes, hierarchy, or formatting preferences." class="field-textarea">{{ old('proof_note') }}</textarea>
                        <span class="text-xs text-[var(--color-text-soft)]">Use this for designer guidance, not the main personalization text itself.</span>
                    </label>
                </div>

                <div class="flex flex-wrap items-end gap-4">
                    <x-storefront.quantity-selector />
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="button-primary">Add personalized order</button>
                        <a href="{{ route('checkout.show') }}" class="button-secondary">Buy now</a>
                    </div>
                </div>
            </form>

            <div class="mt-5 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('wishlist.store', $product) }}">
                    @csrf
                    <button type="submit" class="button-ghost">Save design</button>
                </form>
                <a href="{{ route('products.show', $product) }}" class="button-ghost">Share link</a>
            </div>

            <div class="mt-6 rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] p-5 text-sm leading-7 text-[var(--color-secondary-900)]">
                Delivery and proof timeline: once the order is placed, the structured payload travels with the cart item so proofing and fulfillment can follow the correct ceremonial hierarchy.
            </div>
        </div>
    </div>

    <div class="space-y-6 lg:col-span-2">
        <div class="surface-card p-8">
            <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">How this works</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5 text-sm leading-7 text-[var(--color-text-soft)]">
                    1. Fill the structured personalization fields. 2. Choose your preferred typography style. 3. Add any designer notes for hierarchy or spelling.
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5 text-sm leading-7 text-[var(--color-text-soft)]">
                    4. Add the design to cart with its structured payload. 5. Proof and fulfillment follow after order confirmation.
                </div>
            </div>
        </div>

        <div class="surface-card p-8">
            <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Personalization policy</h2>
            <div class="mt-5 space-y-3 text-sm leading-7 text-[var(--color-text-soft)]">
                <p>All main text should be entered in the dedicated fields so the proof can match the intended zones.</p>
                <p>Designer notes are reviewed as supporting guidance, not as substitutes for the main personalization inputs.</p>
                <p>Because this is a structured ceremonial product, proof and delivery expectations may differ from regular inventory items.</p>
            </div>
        </div>

        @if ($product->reviews->isNotEmpty())
            <div class="surface-card-soft p-6">
                <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Ceremonial feedback</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach ($product->reviews as $review)
                        <x-storefront.review-card :review="$review" />
                    @endforeach
                </div>
            </div>
        @endif

        @if ($product->relatedProducts->isNotEmpty())
            <div class="surface-card-featured p-8">
                <div class="max-w-2xl">
                    <p class="eyebrow">Ceremonial add-ons</p>
                    <h2 class="mt-4 text-2xl font-semibold text-[var(--color-secondary-900)]">Pair with your Nikah order</h2>
                    <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Designed to feel more intentional than generic related products, this section highlights matching ceremonial companions and gifting essentials.</p>
                </div>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @foreach ($product->relatedProducts->take(4) as $relatedProduct)
                        <x-storefront.listing-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.product-detail>
