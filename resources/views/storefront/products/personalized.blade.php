@php
    $activeFonts = $template->fonts
        ->where('is_active', true)
        ->sortBy('sort_order')
        ->values();

    if ($activeFonts->isEmpty()) {
        $activeFonts = $template->fonts->sortBy('sort_order')->values();
    }

    $fontStylesheetUrls = $activeFonts
        ->where('font_source_type', 'google')
        ->pluck('font_source_value')
        ->filter()
        ->unique()
        ->values();

    $defaultFontId = old('font_id', $activeFonts->firstWhere('is_default', true)?->id ?? $activeFonts->first()?->id);
@endphp

@foreach ($fontStylesheetUrls as $fontStylesheetUrl)
    <link rel="stylesheet" href="{{ $fontStylesheetUrl }}">
@endforeach

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
            'image' => $galleryItems->pluck('scene')->filter()->values()->all(),
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
            galleryItems: @js($galleryItems->values()->all()),
            activeSlideId: @js($galleryItems->first()['id'] ?? 'template-flat'),
            selectedFont: @js($defaultFontId),
            sceneFont: @js($defaultFontId),
            fields: @js($template->fields->mapWithKeys(fn ($field) => [$field->field_key => old('personalization.'.$field->field_key, $field->default_value ?? $field->preview_sample_value ?? '')])->all()),
            sceneFields: @js($template->fields->mapWithKeys(fn ($field) => [$field->field_key => old('personalization.'.$field->field_key, $field->default_value ?? $field->preview_sample_value ?? '')])->all()),
            fonts: @js($activeFonts->map(fn ($font) => [
                'id' => $font->id,
                'name' => $font->name,
                'preview_label' => $font->preview_label,
                'category' => $font->category,
                'font_family' => $font->font_family ?: $font->css_font_family,
                'preview_sample_text' => $font->preview_sample_text,
                'font_weight_default' => $font->font_weight_default,
                'font_style_default' => $font->font_style_default,
                'letter_spacing_default' => $font->letter_spacing_default,
                'line_height_default' => $font->line_height_default,
                'text_transform_default' => $font->text_transform_default,
                'recommended_for' => $font->recommended_for,
            ])->values()),
            sceneRefreshTimer: null,
            activeSlide() {
                return this.galleryItems.find((item) => item.id === this.activeSlideId) ?? this.galleryItems[0];
            },
            selectSlide(id) {
                this.activeSlideId = id;
            },
            fontFamily(id) {
                return this.fonts.find((item) => item.id == id)?.font_family ?? 'Poppins, sans-serif';
            },
            polygon(map) {
                return `${map.top_left_x * 100}% ${map.top_left_y * 100}%, ${map.top_right_x * 100}% ${map.top_right_y * 100}%, ${map.bottom_right_x * 100}% ${map.bottom_right_y * 100}%, ${map.bottom_left_x * 100}% ${map.bottom_left_y * 100}%`;
            },
            bounds(map) {
                const xValues = [map.top_left_x, map.top_right_x, map.bottom_right_x, map.bottom_left_x];
                const yValues = [map.top_left_y, map.top_right_y, map.bottom_right_y, map.bottom_left_y];
                const minX = Math.min(...xValues);
                const maxX = Math.max(...xValues);
                const minY = Math.min(...yValues);
                const maxY = Math.max(...yValues);

                return {
                    left: minX,
                    top: minY,
                    width: Math.max(0.12, maxX - minX),
                    height: Math.max(0.12, maxY - minY),
                };
            },
            previewStyle(map) {
                const bounds = this.bounds(map);

                return `left:${bounds.left * 100}%; top:${bounds.top * 100}%; width:${bounds.width * 100}%; height:${bounds.height * 100}%; clip-path: polygon(${this.polygon(map)}); opacity:${map.opacity}; filter: drop-shadow(0 18px 28px rgba(0,48,73,${Math.max(0.12, map.shadow_strength) * 0.35}));`;
            },
            scheduleSceneRefresh() {
                window.clearTimeout(this.sceneRefreshTimer);
                this.sceneRefreshTimer = window.setTimeout(() => {
                    this.sceneFields = JSON.parse(JSON.stringify(this.fields));
                    this.sceneFont = this.selectedFont;
                }, 220);
            },
            flushSceneRefresh() {
                window.clearTimeout(this.sceneRefreshTimer);
                this.sceneFields = JSON.parse(JSON.stringify(this.fields));
                this.sceneFont = this.selectedFont;
            },
        }"
        x-init="$watch('selectedFont', () => scheduleSceneRefresh())"
    >
        <div class="surface-configurator overflow-hidden p-6 lg:sticky lg:top-28 lg:self-start">
            <div class="flex items-center justify-between gap-4">
                <x-storefront.product-breadcrumbs :product="$product" />
                <span class="rounded-full bg-white/80 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-primary-900)]">
                    Signature configurator
                </span>
            </div>

            <div class="mt-6 rounded-[var(--radius-3xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)] p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Preview gallery</p>
                        <h2 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]" x-text="activeSlide()?.title"></h2>
                    </div>
                    <p class="rounded-full bg-white px-4 py-2 text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]" x-text="activeSlide()?.eyebrow"></p>
                </div>

                <div class="mt-5 relative overflow-hidden rounded-[var(--radius-2xl)] border border-white/70 bg-white shadow-[0_20px_50px_rgba(15,46,60,0.12)]">
                    <div
                        x-show="activeSlide()?.kind === 'flat'"
                        class="relative mx-auto aspect-[9/13] max-h-[720px] w-full max-w-[560px] overflow-hidden bg-white"
                    >
                        <img :src="activeSlide()?.scene" alt="{{ $template->name }}" class="h-full w-full object-cover" fetchpriority="high" decoding="async">

                        @foreach ($template->fields as $field)
                            <div
                                class="absolute px-2 text-center text-[var(--color-primary-900)]"
                                style="
                                    left: {{ $field->position_x }}%;
                                    top: {{ $field->position_y }}%;
                                    width: {{ $field->width }}%;
                                    transform: translate(-50%, -50%) rotate({{ (float) $field->rotation }}deg);
                                    text-align: {{ $field->text_align === 'start' ? 'left' : ($field->text_align === 'end' ? 'right' : 'center') }};
                                    color: {{ $field->text_color }};
                                    min-height: {{ $field->height }}%;
                                    line-height: {{ $field->line_height }};
                                    letter-spacing: {{ $field->letter_spacing }}px;
                                    font-size: clamp({{ $field->font_size_min }}px, 1.8vw, {{ $field->font_size_max }}px);
                                    z-index: {{ $field->z_index ?? 1 }};
                                "
                                :style="`font-family: ${fontFamily(selectedFont)}`"
                                x-text="fields['{{ $field->field_key }}'] || '{{ $field->placeholder }}'"
                            ></div>
                        @endforeach
                    </div>

                    <div
                        x-show="activeSlide()?.kind === 'mockup'"
                        class="relative aspect-[4/3] w-full overflow-hidden bg-white"
                    >
                        <img :src="activeSlide()?.scene" alt="" class="h-full w-full object-cover">

                        <div
                            x-show="activeSlide()?.map"
                            class="absolute inset-0 pointer-events-none"
                            :style="previewStyle(activeSlide().map)"
                        >
                            <div class="relative h-full w-full overflow-hidden rounded-[18px] border border-[rgba(120,0,0,0.14)] bg-white/92">
                                <img :src="@js($template->preview_image_url ?: $template->base_template_url)" alt="{{ $template->name }}" class="h-full w-full object-cover">

                                @foreach ($template->fields as $field)
                                    <div
                                        class="absolute px-2 text-center text-[var(--color-primary-900)]"
                                        style="
                                            left: {{ $field->position_x }}%;
                                            top: {{ $field->position_y }}%;
                                            width: {{ $field->width }}%;
                                            transform: translate(-50%, -50%) rotate({{ (float) $field->rotation }}deg);
                                            text-align: {{ $field->text_align === 'start' ? 'left' : ($field->text_align === 'end' ? 'right' : 'center') }};
                                            color: {{ $field->text_color }};
                                            min-height: {{ $field->height }}%;
                                            line-height: {{ $field->line_height }};
                                            letter-spacing: {{ $field->letter_spacing }}px;
                                            font-size: clamp(8px, 1vw, {{ $field->font_size_max }}px);
                                            z-index: {{ $field->z_index ?? 1 }};
                                        "
                                        :style="`font-family: ${fontFamily(sceneFont)}`"
                                        x-text="sceneFields['{{ $field->field_key }}'] || '{{ $field->placeholder }}'"
                                    ></div>
                                @endforeach
                            </div>
                        </div>

                        <img
                            x-show="activeSlide()?.overlay"
                            :src="activeSlide()?.overlay"
                            alt=""
                            class="pointer-events-none absolute inset-0 h-full w-full object-cover"
                            :style="`opacity:${Math.max(0.12, activeSlide()?.map?.highlight_strength ?? 0.16)}`"
                        >
                        <img
                            x-show="activeSlide()?.mask"
                            :src="activeSlide()?.mask"
                            alt=""
                            class="pointer-events-none absolute inset-0 h-full w-full object-cover mix-blend-multiply"
                            :style="`opacity:${Math.max(0.12, (activeSlide()?.map?.highlight_strength ?? 0.16) * 0.9)}`"
                        >
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <template x-for="item in galleryItems" :key="item.id">
                        <button
                            type="button"
                            class="overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white text-left transition hover:-translate-y-0.5 hover:shadow-[0_16px_30px_rgba(15,46,60,0.08)]"
                            :class="{ 'border-[var(--color-primary-900)] shadow-[0_18px_30px_rgba(120,0,0,0.14)]': activeSlideId === item.id }"
                            @click="selectSlide(item.id)"
                        >
                            <img :src="item.thumb || item.scene" :alt="item.title" class="aspect-[4/3] w-full object-cover">
                            <div class="p-3">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--color-primary-900)]" x-text="item.eyebrow"></p>
                                <p class="mt-2 text-sm font-semibold leading-6 text-[var(--color-secondary-900)]" x-text="item.title"></p>
                            </div>
                        </button>
                    </template>
                </div>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-[var(--radius-xl)] bg-white/80 px-4 py-4 text-sm text-[var(--color-text-soft)]">
                    <p>Flat certificate updates instantly as you type. Scene previews follow the same template artwork.</p>
                    <p>{{ $galleryItems->count() }} curated view{{ $galleryItems->count() === 1 ? '' : 's' }} for this Nikah product.</p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="surface-sidebar p-8">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="eyebrow">Advanced Personalized</span>
                    @if ($product->category)
                        <x-storefront.trust-badge :label="$product->category->name" />
                    @endif
                    <x-storefront.trust-badge label="Made to order" />
                    <x-storefront.trust-badge label="Proof-aware workflow" />
                </div>

                <h1 class="mt-5 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">{{ $product->name }}</h1>
                <div class="mt-6">
                    <x-storefront.price-block :product="$product" />
                </div>
                <p class="mt-4 text-base leading-8 text-[var(--color-text-soft)]">{{ $product->description }}</p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <span class="info-pill">Structured field inputs</span>
                    <span class="info-pill">Font presets</span>
                    <span class="info-pill">Scene mockup gallery</span>
                </div>

                <form method="POST" action="{{ route('cart.store', $product) }}" class="mt-8 space-y-6">
                    @csrf

                    <div class="surface-configurator p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold text-[var(--color-secondary-900)]">Structured personalization</h2>
                            <span class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Template-safe text zones</span>
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
                                        @input="scheduleSceneRefresh()"
                                        @blur="flushSceneRefresh()"
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
                            <span class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Curated presets</span>
                        </div>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($activeFonts as $font)
                                <label class="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="font_id"
                                        value="{{ $font->id }}"
                                        class="peer sr-only"
                                        x-model="selectedFont"
                                        @change="flushSceneRefresh()"
                                        @checked($defaultFontId == $font->id)
                                    >
                                    <span class="relative flex min-h-40 flex-col justify-between rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white px-4 py-4 text-sm transition hover:-translate-y-0.5 hover:shadow-[0_14px_26px_rgba(15,46,60,0.08)] peer-checked:border-[var(--color-primary-900)] peer-checked:bg-[var(--color-surface-cream)] peer-checked:shadow-[0_18px_35px_rgba(120,0,0,0.12)]">
                                        <span class="rounded-full bg-[rgba(253,240,213,0.95)] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">{{ $font->category }}</span>
                                        <span
                                            style="font-family: {{ $font->font_family ?: $font->css_font_family }}; font-weight: {{ $font->font_weight_default ?? '600' }}; font-style: {{ $font->font_style_default ?? 'normal' }}; letter-spacing: {{ $font->letter_spacing_default ?? 0 }}px; line-height: {{ $font->line_height_default ?? 1.2 }}; text-transform: {{ $font->text_transform_default ?? 'none' }};"
                                            class="mt-5 flex min-h-16 items-center justify-center text-center text-2xl text-[var(--color-primary-900)]"
                                        >{{ $font->preview_sample_text ?: ($font->preview_label ?: $font->name) }}</span>
                                        <span class="mt-4 text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-secondary-900)]">{{ $font->preview_label ?: $font->name }}</span>
                                        <span class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $font->recommended_for === 'all' ? 'Works across all lines' : str($font->recommended_for)->replace('_', ' ')->headline() }}</span>
                                        <span class="pointer-events-none absolute bottom-3 right-3 hidden h-8 w-8 items-center justify-center rounded-full bg-[var(--color-primary-900)] text-sm font-bold text-white peer-checked:flex">✓</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-4 text-sm text-[var(--color-text-soft)]">Typography updates live in the certificate preview.</p>
                    </div>

                    <div class="surface-configurator p-5">
                        <label class="field-shell">
                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $template->proof_note_label ?: 'Proof notes' }}</span>
                            <textarea name="proof_note" rows="4" placeholder="Mention any spelling notes, hierarchy, or formatting preferences." class="field-textarea">{{ old('proof_note') }}</textarea>
                            <span class="text-xs text-[var(--color-text-soft)]">Use this for designer guidance, not the main personalization text itself.</span>
                        </label>
                    </div>

                    <x-storefront.quantity-selector />

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="button-primary">Add personalized order</button>
                        <a href="{{ route('checkout.show') }}" class="button-secondary">Buy now</a>
                    </div>
                </form>

                <div class="mt-4 flex flex-wrap gap-3">
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

            <div class="surface-card p-8">
                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <p class="eyebrow">How it works</p>
                        <h2 class="mt-4 text-2xl font-semibold text-[var(--color-secondary-900)]">From field entry to proof-ready Nikah output</h2>
                        <div class="mt-5 space-y-3 text-sm leading-7 text-[var(--color-text-soft)]">
                            <p>1. Enter the structured ceremonial text into the dedicated fields.</p>
                            <p>2. Choose a font preset that matches your ceremony style.</p>
                            <p>3. Review the flat certificate preview and optional lifestyle scene mockups.</p>
                            <p>4. Submit the order with proof notes so the design team can finalize accurately.</p>
                        </div>
                    </div>
                    <div>
                        <p class="eyebrow">FAQ</p>
                        <div class="mt-4 space-y-4">
                            <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5">
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Will the scene mockups match the flat certificate?</p>
                                <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Yes. The certificate template remains the source of truth, and the same artwork is mapped into the selected scene previews.</p>
                            </div>
                            <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5">
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Can I mention hierarchy or spelling notes?</p>
                                <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">{{ $template->instructions ?: 'Yes. Use the proof note box for supporting guidance while keeping the main ceremonial text inside the structured fields.' }}</p>
                            </div>
                            <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5">
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Are there safe-zone considerations?</p>
                                <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">{{ $template->safe_zone_notes ?: 'The preview honors the defined safe zones so designer proofing stays aligned with the final certificate structure.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($product->relatedCategories->isNotEmpty())
                <div class="surface-card p-8">
                    <p class="eyebrow">Related categories</p>
                    <h2 class="mt-4 text-2xl font-semibold text-[var(--color-secondary-900)]">Explore more ceremonial directions</h2>
                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($product->relatedCategories->take(3) as $relatedCategory)
                            <x-storefront.category-tile :category="$relatedCategory" />
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($product->reviews->isNotEmpty())
                <div class="surface-card-soft p-6">
                    <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Ceremonial feedback</h2>
                    <div class="mt-5 grid gap-4">
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
    </div>
</x-layouts.product-detail>
