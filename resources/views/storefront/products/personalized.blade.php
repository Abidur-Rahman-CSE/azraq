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

    $editorialFontUrl = 'https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700';
    $defaultFontId = old('font_id', $activeFonts->firstWhere('is_default', true)?->id ?? $activeFonts->first()?->id);
    $defaultMockupId = old('mockup_id', $availableMockups->firstWhere('pivot.is_default', true)?->id ?? $availableMockups->first()?->id);
    $compareAtPrice = $product->compare_at_price ? (float) $product->compare_at_price : null;
    $leadTimeDays = max(4, (int) ($product->lead_time_days ?: 5));
    $shortDescription = filled($product->excerpt) && ! str($product->excerpt)->contains('structured personalization flow')
        ? $product->excerpt
        : 'A refined Nikah Nama certificate with live personalization, curated typography, and proof review before production.';
    $storyDescription = filled($product->description) && ! str($product->description)->contains('Phase 1 sample')
        ? $product->description
        : 'Created as a keepsake-worthy record of your ceremony, this Nikah Nama balances traditional formality with a warm, display-ready presentation designed for gifting, framing, and long-term preservation.';
    $includedItems = [
        'Personalized Nikah Nama certificate with your submitted names, date, and venue',
        'One proof review before final production begins',
        'Premium print finish on an ivory-toned ceremonial presentation surface',
        'Curated font preset selection and live mockup proof preview',
        'Careful protective packaging suitable for keepsake gifting',
    ];
    $productionNotes = [
        [
            'label' => 'Proof review',
            'value' => 'Sent before final production',
            'copy' => 'We review your submitted personalization and prepare a proof with the selected scene for approval.',
        ],
        [
            'label' => 'Production window',
            'value' => $leadTimeDays.' to '.($leadTimeDays + 2).' business days',
            'copy' => 'Final production starts only after proof approval so ceremonial details stay accurate.',
        ],
        [
            'label' => 'Packaging',
            'value' => 'Carefully packaged',
            'copy' => 'Your certificate is wrapped for a polished arrival and safer transit.',
        ],
    ];
    $faqItems = collect([
        [
            'question' => 'How does the proof review work?',
            'answer' => 'After checkout, your submitted names, date, venue, and proof notes are reviewed and a custom proof is prepared before final production starts.',
        ],
        [
            'question' => 'Will long names still fit elegantly?',
            'answer' => $template->safe_zone_notes ?: 'Yes. The live preview is designed to scale text within the certificate safe zones, and multiline wrapping is used where the template allows it.',
        ],
        [
            'question' => 'Can I request hierarchy, spelling, or formatting changes?',
            'answer' => $template->instructions ?: 'Yes. Use the proof note field for spelling guidance, honorifics, line order, or styling preferences while keeping the main content in the personalization fields.',
        ],
        [
            'question' => 'Which scene is used for my proof preview?',
            'answer' => 'The mockup scene you choose becomes the active lifestyle proof view used in the cart summary and initial proofing context.',
        ],
    ])->filter(fn ($item) => filled($item['answer']))->values();
@endphp

<x-layouts.product-detail
    :title="$product->name.' | '.config('brand.name')"
    :description="$product->meta_description ?: ($product->excerpt ?: $storyDescription)"
    :social-image="$template->preview_image_url"
    :schema-data="[
        [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->meta_description ?: ($product->excerpt ?: $storyDescription),
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
    <x-slot:head>
        <link rel="stylesheet" href="{{ $editorialFontUrl }}">
        @foreach ($fontStylesheetUrls as $fontStylesheetUrl)
            <link rel="stylesheet" href="{{ $fontStylesheetUrl }}">
        @endforeach
    </x-slot:head>

    <div
        class="lg:col-span-2 grid gap-8 pb-24 xl:gap-10 lg:grid-cols-[minmax(0,1.08fr)_minmax(360px,0.92fr)] lg:pb-0"
        x-data="nikahMockupPreview({
            galleryItems: @js($galleryItems->values()->all()),
            activeSlideId: @js($galleryItems->first()['id'] ?? 'template-flat'),
            selectedMockupId: @js($defaultMockupId),
            selectedFont: @js($defaultFontId),
            fields: @js($template->fields->mapWithKeys(fn ($field) => [$field->field_key => old('personalization.'.$field->field_key, $field->default_value ?? $field->preview_sample_value ?? '')])->all()),
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
            templateImageUrl: @js($template->preview_image_url ?: $template->base_template_url),
            templateFields: @php
                $allFields = [];
                foreach ($template->fields as $field) {
                    $allFields[] = [
                        'field_key'    => $field->field_key,
                        'label'        => $field->label,
                        'placeholder'  => $field->placeholder,
                        'position_x'   => (float) $field->position_x,
                        'position_y'   => (float) $field->position_y,
                        'width'        => (float) $field->width,
                        'height'       => (float) $field->height,
                        'rotation'     => (float) $field->rotation,
                        'text_align'   => $field->text_align,
                        'text_color'   => $field->text_color,
                        'line_height'  => (float) $field->line_height,
                        'letter_spacing' => (float) $field->letter_spacing,
                        'font_size_min'  => (int) $field->font_size_min,
                        'font_size_max'  => (int) $field->font_size_max,
                        'z_index'      => (int) ($field->z_index ?? 1),
                        'settings'     => $field->settings ?? [],
                    ];
                    // Inject virtual Bangla companion field
                    if (str_contains($field->field_key, 'date') && data_get($field->settings, 'auto_bangla')) {
                        $allFields[] = [
                            'field_key'    => $field->field_key . '_bangla',
                            'label'        => 'Bangla date',
                            'placeholder'  => '',
                            'position_x'   => (float) data_get($field->settings, 'bangla_pos_x', 50),
                            'position_y'   => (float) data_get($field->settings, 'bangla_pos_y', 0),
                            'width'        => (float) data_get($field->settings, 'bangla_width', 70),
                            'height'       => (float) data_get($field->settings, 'bangla_height', 8),
                            'rotation'     => 0,
                            'text_align'   => 'center',
                            'text_color'   => data_get($field->settings, 'bangla_color', '#780000'),
                            'line_height'  => (float) $field->line_height,
                            'letter_spacing' => (float) $field->letter_spacing,
                            'font_size_min'  => (int) data_get($field->settings, 'bangla_font_size_min', 10),
                            'font_size_max'  => (int) data_get($field->settings, 'bangla_font_size_max', 16),
                            'z_index'      => (int) ($field->z_index ?? 1) + 1,
                            'settings'     => ['auto_fit' => true, 'allow_multiline' => false, 'max_lines' => 1, 'overflow_behavior' => 'shrink_only', 'font_weight' => data_get($field->settings, 'font_weight', '600'), 'text_transform' => 'none'],
                        ];
                    }
                    // Inject virtual Arabic/Hijri companion field
                    if (str_contains($field->field_key, 'date') && data_get($field->settings, 'auto_arabic')) {
                        $allFields[] = [
                            'field_key'    => $field->field_key . '_arabic',
                            'label'        => 'Arabic date',
                            'placeholder'  => '',
                            'position_x'   => (float) data_get($field->settings, 'arabic_pos_x', 50),
                            'position_y'   => (float) data_get($field->settings, 'arabic_pos_y', 0),
                            'width'        => (float) data_get($field->settings, 'arabic_width', 70),
                            'height'       => (float) data_get($field->settings, 'arabic_height', 8),
                            'rotation'     => 0,
                            'text_align'   => 'center',
                            'text_color'   => data_get($field->settings, 'arabic_color', '#3D3730'),
                            'line_height'  => (float) $field->line_height,
                            'letter_spacing' => (float) $field->letter_spacing,
                            'font_size_min'  => (int) data_get($field->settings, 'arabic_font_size_min', 10),
                            'font_size_max'  => (int) data_get($field->settings, 'arabic_font_size_max', 14),
                            'z_index'      => (int) ($field->z_index ?? 1) + 2,
                            'settings'     => ['auto_fit' => true, 'allow_multiline' => false, 'max_lines' => 1, 'overflow_behavior' => 'shrink_only', 'font_weight' => data_get($field->settings, 'font_weight', '600'), 'text_transform' => 'none'],
                        ];
                    }
                }
            @endphp @js($allFields),
        })"
    >
        <section class="space-y-5 lg:sticky lg:top-28 lg:self-start">
            <x-storefront.product-breadcrumbs :product="$product" />

            <article class="surface-product overflow-hidden p-5 sm:p-6 lg:p-7">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-[var(--color-text-soft)]">Preview gallery</p>
                        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.26em] text-[var(--color-primary-900)]">Live certificate preview</p>
                        <h2
                            class="mt-3 text-[2rem] leading-none text-[var(--color-secondary-900)] sm:text-[2.5rem]"
                            style="font-family: 'Cormorant Garamond', serif;"
                            x-text="activeSlide()?.title"
                        ></h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] transition"
                            :class="activeSlide()?.kind === 'flat'
                                ? 'border-[var(--color-primary-900)] bg-[var(--color-primary-900)] text-white shadow-[0_16px_35px_rgba(120,0,0,0.2)]'
                                : 'border-[var(--color-border-soft)] bg-white text-[var(--color-secondary-900)] hover:border-[rgba(120,0,0,0.22)]'"
                            :aria-pressed="activeSlide()?.kind === 'flat'"
                            @click="showFlatPreview()"
                        >
                            Flat preview
                        </button>
                        <button
                            x-show="hasMockupSlides()"
                            type="button"
                            class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] transition"
                            :class="activeSlide()?.kind === 'mockup'
                                ? 'border-[var(--color-secondary-900)] bg-[var(--color-secondary-900)] text-white shadow-[0_16px_35px_rgba(0,48,73,0.16)]'
                                : 'border-[var(--color-border-soft)] bg-white text-[var(--color-secondary-900)] hover:border-[rgba(0,48,73,0.22)]'"
                            :aria-pressed="activeSlide()?.kind === 'mockup'"
                            @click="showSelectedMockupPreview()"
                        >
                            Scene preview
                        </button>
                    </div>
                </div>

                <div class="mt-5 rounded-[calc(var(--radius-3xl)+4px)] border border-[rgba(120,0,0,0.08)] bg-[linear-gradient(180deg,rgba(253,248,240,0.96),rgba(247,241,231,0.96))] p-3 sm:p-4">
                    <div class="rounded-[var(--radius-3xl)] border border-[rgba(0,48,73,0.08)] bg-[#fffdf9] p-4 shadow-[0_28px_60px_rgba(15,46,60,0.09)] sm:p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-[var(--radius-xl)] border border-[rgba(0,48,73,0.08)] bg-[rgba(253,248,240,0.72)] px-4 py-3">
                            <div>
                                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.24em] text-[var(--color-text-soft)]">Preview mode</p>
                                <p class="mt-1 text-sm font-semibold text-[var(--color-secondary-900)]" x-text="activeSlide()?.eyebrow"></p>
                            </div>
                            <p class="text-sm text-[var(--color-text-soft)]">Your names and date update live in the preview</p>
                        </div>

                        <div class="mt-4 overflow-hidden rounded-[28px] border border-[rgba(0,48,73,0.08)] bg-[#fcfaf5]">
                            <div
                                x-show="activeSlide()?.kind === 'flat'"
                                x-transition.opacity.duration.250ms
                                x-ref="flatStage"
                                class="relative aspect-[10/13] w-full bg-[#fdfaf2]"
                            >
                                <canvas
                                    x-ref="flatCanvas"
                                    class="block h-full w-full"
                                    aria-label="Flat preview of the personalized Nikah Nama certificate"
                                ></canvas>
                            </div>

                            <div
                                x-show="activeSlide()?.kind === 'mockup'"
                                x-transition.opacity.duration.250ms
                                x-ref="mockupStage"
                                class="relative aspect-[4/3] w-full bg-white"
                            >
                                <canvas
                                    x-ref="mockupCanvas"
                                    class="block h-full w-full"
                                    aria-label="Perspective preview of the selected Nikah Nama mockup"
                                ></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex gap-3 overflow-x-auto pb-2">
                    <template x-for="item in galleryItems" :key="item.id">
                        <button
                            type="button"
                            class="w-36 flex-none overflow-hidden rounded-[var(--radius-xl)] border bg-white text-left transition hover:-translate-y-0.5 hover:shadow-[0_16px_28px_rgba(15,46,60,0.09)]"
                            :class="activeSlideId === item.id
                                ? 'border-[var(--color-primary-900)] shadow-[0_18px_35px_rgba(120,0,0,0.14)]'
                                : 'border-[var(--color-border-soft)]'"
                            :aria-pressed="activeSlideId === item.id"
                            @click="selectSlide(item.id)"
                        >
                            <img :src="item.thumb || item.scene" :alt="item.title" class="aspect-[4/3] w-full object-cover">
                            <div class="space-y-1 p-3">
                                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-[var(--color-primary-900)]" x-text="item.eyebrow"></p>
                                <p class="text-sm font-semibold leading-5 text-[var(--color-secondary-900)]" x-text="item.title"></p>
                            </div>
                        </button>
                    </template>
                </div>

                <div class="mt-4 rounded-[var(--radius-2xl)] border border-[rgba(120,0,0,0.08)] bg-[rgba(253,240,213,0.4)] px-4 py-4 text-sm text-[var(--color-secondary-900)]">
                    <p class="font-semibold">Your names and date update live in the preview</p>
                    <p class="mt-1 text-[var(--color-text-soft)]">Flat proofing stays crisp, and selected lifestyle scenes use the same perspective-correct certificate artwork.</p>
                </div>
            </article>
        </section>

        <aside class="space-y-5">
            <section class="surface-sidebar p-6 sm:p-8">
                <div class="flex flex-wrap gap-2.5">
                    <span class="eyebrow">Made to order</span>
                    <span class="info-pill !bg-[rgba(120,0,0,0.08)] !text-[var(--color-primary-900)]">Proof review included</span>
                    <span class="info-pill">Premium print finish</span>
                </div>

                <h1
                    class="mt-5 text-[2.6rem] leading-[0.95] tracking-[-0.03em] text-[var(--color-secondary-900)] sm:text-[3.2rem]"
                    style="font-family: 'Cormorant Garamond', serif;"
                >
                    {{ $product->name }}
                </h1>

                <div class="mt-6 rounded-[var(--radius-2xl)] border border-[rgba(0,48,73,0.08)] bg-white/90 px-5 py-5 shadow-[0_18px_40px_rgba(15,46,60,0.06)]">
                    <div class="flex flex-wrap items-end gap-3">
                        <p class="text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">BDT {{ number_format((float) $product->price, 0) }}</p>
                        @if ($compareAtPrice)
                            <p class="pb-1 text-lg text-[var(--color-text-soft)] line-through">BDT {{ number_format($compareAtPrice, 0) }}</p>
                        @endif
                    </div>
                    <p class="mt-2 text-sm font-medium text-[var(--color-primary-900)]">Custom proof included before final production.</p>
                </div>

                <p class="mt-5 text-base leading-8 text-[var(--color-text-soft)]">{{ $shortDescription }}</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-[var(--radius-xl)] border border-[rgba(0,48,73,0.08)] bg-white/75 px-4 py-4">
                        <p class="text-[0.68rem] uppercase tracking-[0.22em] text-[var(--color-text-soft)]">Proofing</p>
                        <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">Live preview + review</p>
                    </div>
                    <div class="rounded-[var(--radius-xl)] border border-[rgba(0,48,73,0.08)] bg-white/75 px-4 py-4">
                        <p class="text-[0.68rem] uppercase tracking-[0.22em] text-[var(--color-text-soft)]">Production</p>
                        <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $leadTimeDays }} to {{ $leadTimeDays + 2 }} days</p>
                    </div>
                    <div class="rounded-[var(--radius-xl)] border border-[rgba(0,48,73,0.08)] bg-white/75 px-4 py-4">
                        <p class="text-[0.68rem] uppercase tracking-[0.22em] text-[var(--color-text-soft)]">Presentation</p>
                        <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">Keepsake-ready finish</p>
                    </div>
                </div>
            </section>

            <form method="POST" action="{{ route('cart.store', $product) }}" class="space-y-5">
                @csrf

                <section class="surface-configurator p-5 sm:p-6">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Personalization</p>
                            <h2
                                class="mt-2 text-[1.9rem] leading-none text-[var(--color-secondary-900)] sm:text-[2.1rem]"
                                style="font-family: 'Cormorant Garamond', serif;"
                            >
                                Enter your certificate details
                            </h2>
                        </div>
                        <p class="text-sm text-[var(--color-text-soft)]">Structured fields keep the proof aligned to the template.</p>
                    </div>

                    <div class="mt-5 grid gap-4">
                        @foreach ($template->fields as $field)
                            @php
                                $fieldError = $errors->first('personalization.'.$field->field_key);
                                $isMultiline = data_get($field->settings, 'allow_multiline', true) && (int) data_get($field->settings, 'max_lines', 1) > 1;
                            @endphp
                            <label class="field-shell rounded-[var(--radius-2xl)] border {{ $fieldError ? 'border-[rgba(180,35,24,0.35)] bg-[rgba(180,35,24,0.04)]' : 'border-[rgba(0,48,73,0.08)] bg-white/85' }} px-4 py-4">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $field->label }}</span>
                                    <span class="text-[0.7rem] uppercase tracking-[0.18em] text-[var(--color-text-soft)]">
                                        {{ $field->is_required ? 'Required' : 'Optional' }}
                                        @if ($field->max_length)
                                            · {{ $field->max_length }} char
                                        @endif
                                    </span>
                                </div>

                                @if ($isMultiline)
                                    <textarea
                                        name="personalization[{{ $field->field_key }}]"
                                        rows="{{ min(4, max(2, (int) data_get($field->settings, 'max_lines', 3))) }}"
                                        maxlength="{{ $field->max_length }}"
                                        placeholder="{{ $field->placeholder }}"
                                        x-model="fields['{{ $field->field_key }}']"
                                        @input="scheduleSceneRefresh()"
                                        @blur="flushSceneRefresh()"
                                        @required($field->is_required)
                                        class="field-textarea {{ $fieldError ? '!border-[var(--color-danger)] !bg-white' : '' }}"
                                    >{{ old('personalization.'.$field->field_key, $field->default_value ?? $field->preview_sample_value ?? '') }}</textarea>
                                @else
                                    <input
                                        type="text"
                                        name="personalization[{{ $field->field_key }}]"
                                        maxlength="{{ $field->max_length }}"
                                        value="{{ old('personalization.'.$field->field_key, $field->default_value ?? $field->preview_sample_value ?? '') }}"
                                        placeholder="{{ $field->placeholder }}"
                                        x-model="fields['{{ $field->field_key }}']"
                                        @input="scheduleSceneRefresh()"
                                        @blur="flushSceneRefresh()"
                                        @required($field->is_required)
                                        class="field-input {{ $fieldError ? '!border-[var(--color-danger)] !bg-white' : '' }}"
                                    >
                                @endif

                                @if ($field->help_text || $fieldError)
                                    <div class="flex flex-wrap items-start justify-between gap-2 text-xs">
                                        @if ($field->help_text)
                                            <span class="text-[var(--color-text-soft)]">{{ $field->help_text }}</span>
                                        @endif
                                        @if ($fieldError)
                                            <span class="font-medium text-[var(--color-danger)]">{{ $fieldError }}</span>
                                        @endif
                                    </div>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </section>

                @if ($activeFonts->isNotEmpty() && $product->font_presets_enabled !== false)
                    <section class="surface-configurator p-5 sm:p-6">
                        <div class="flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Font presets</p>
                                <h2
                                    class="mt-2 text-[1.9rem] leading-none text-[var(--color-secondary-900)] sm:text-[2.1rem]"
                                    style="font-family: 'Cormorant Garamond', serif;"
                                >
                                    Choose a font
                                </h2>
                            </div>
                            <p class="text-sm text-[var(--color-text-soft)]">Your selected preset updates the preview immediately.</p>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
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
                                    <span class="relative flex h-full min-h-40 flex-col justify-between rounded-[var(--radius-2xl)] border border-[var(--color-border-soft)] bg-white px-4 py-4 transition hover:-translate-y-0.5 hover:shadow-[0_16px_28px_rgba(15,46,60,0.08)] peer-checked:border-[var(--color-primary-900)] peer-checked:bg-[rgba(253,240,213,0.42)] peer-checked:shadow-[0_20px_36px_rgba(120,0,0,0.12)]">
                                        <span class="flex items-center justify-between gap-3">
                                            <span class="rounded-full bg-[rgba(0,48,73,0.06)] px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-900)]">{{ $font->category }}</span>
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border text-sm font-bold transition {{ $defaultFontId == $font->id ? 'border-[var(--color-primary-900)] bg-[var(--color-primary-900)] text-white' : 'border-[rgba(0,48,73,0.12)] text-[var(--color-text-soft)]' }}" :class="selectedFont == '{{ $font->id }}' ? 'border-[var(--color-primary-900)] bg-[var(--color-primary-900)] text-white' : 'border-[rgba(0,48,73,0.12)] text-[var(--color-text-soft)]'">✓</span>
                                        </span>
                                        <span
                                            class="mt-5 block text-center text-[1.75rem] text-[var(--color-primary-900)]"
                                            style="font-family: {{ $font->font_family ?: $font->css_font_family }}; font-weight: {{ $font->font_weight_default ?? '600' }}; font-style: {{ $font->font_style_default ?? 'normal' }}; letter-spacing: {{ $font->letter_spacing_default ?? 0 }}px; line-height: {{ $font->line_height_default ?? 1.2 }}; text-transform: {{ $font->text_transform_default ?? 'none' }};"
                                        >
                                            {{ $font->preview_sample_text ?: ($font->preview_label ?: $font->name) }}
                                        </span>
                                        <span class="mt-5 block text-sm font-semibold text-[var(--color-secondary-900)]">{{ $font->preview_label ?: $font->name }}</span>
                                        <span class="mt-1 block text-xs text-[var(--color-text-soft)]">{{ $font->recommended_for === 'all' ? 'Balanced for the full certificate' : str($font->recommended_for)->replace('_', ' ')->headline() }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @error('font_id')
                            <p class="mt-3 text-sm font-medium text-[var(--color-danger)]">{{ $message }}</p>
                        @enderror
                    </section>
                @endif

                @if ($availableMockups->isNotEmpty())
                    <input type="hidden" name="mockup_id" :value="selectedMockupId">

                    <section class="surface-configurator p-5 sm:p-6">
                        <div class="flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Proof scene</p>
                                <h2
                                    class="mt-2 text-[1.9rem] leading-none text-[var(--color-secondary-900)] sm:text-[2.1rem]"
                                    style="font-family: 'Cormorant Garamond', serif;"
                                >
                                    Choose the mockup scene
                                </h2>
                            </div>
                            <p class="text-sm text-[var(--color-text-soft)]">This scene becomes the active proof-preview view.</p>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($availableMockups as $mockup)
                                <button
                                    type="button"
                                    class="rounded-[var(--radius-2xl)] border border-[var(--color-border-soft)] bg-white p-3 text-left transition hover:-translate-y-0.5 hover:shadow-[0_16px_28px_rgba(15,46,60,0.08)]"
                                    :class="String(selectedMockupId) === '{{ $mockup->id }}'
                                        ? 'border-[var(--color-primary-900)] bg-[rgba(253,240,213,0.42)] shadow-[0_18px_35px_rgba(120,0,0,0.12)]'
                                        : ''"
                                    :aria-pressed="String(selectedMockupId) === '{{ $mockup->id }}'"
                                    @click="selectMockup({{ $mockup->id }})"
                                >
                                    <div class="overflow-hidden rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)]">
                                        <img src="{{ $mockup->thumb_image_url ?: $mockup->base_image_url }}" alt="{{ $mockup->title }}" class="aspect-[4/3] w-full object-cover">
                                    </div>
                                    <div class="mt-3 flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $mockup->title }}</p>
                                            <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ str($mockup->render_mode)->headline() }}</p>
                                        </div>
                                        <span
                                            x-show="String(selectedMockupId) === '{{ $mockup->id }}'"
                                            class="rounded-full bg-[var(--color-primary-900)] px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-white"
                                        >
                                            Selected
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>

                        @error('mockup_id')
                            <p class="mt-3 text-sm font-medium text-[var(--color-danger)]">{{ $message }}</p>
                        @enderror
                    </section>
                @endif

                <section class="surface-configurator p-5 sm:p-6">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Proof notes</p>
                            <h2
                                class="mt-2 text-[1.9rem] leading-none text-[var(--color-secondary-900)] sm:text-[2.1rem]"
                                style="font-family: 'Cormorant Garamond', serif;"
                            >
                                {{ $template->proof_note_label ?: 'Add optional proof notes' }}
                            </h2>
                        </div>
                        <p class="text-sm text-[var(--color-text-soft)]">Mention any spelling, hierarchy, or formatting preferences.</p>
                    </div>

                    <label class="field-shell mt-5">
                        <textarea
                            name="proof_note"
                            rows="4"
                            maxlength="500"
                            placeholder="Mention any spelling, hierarchy, or formatting preferences."
                            class="field-textarea @error('proof_note') !border-[var(--color-danger)] !bg-white @enderror"
                        >{{ old('proof_note') }}</textarea>
                        <span class="text-xs text-[var(--color-text-soft)]">Use this for designer guidance rather than the main certificate wording itself.</span>
                        @error('proof_note')
                            <span class="text-xs font-medium text-[var(--color-danger)]">{{ $message }}</span>
                        @enderror
                    </label>
                </section>

                <section id="order-panel" class="surface-card-featured p-5 sm:p-6" x-data="{ qty: Number(@js((int) old('quantity', 1))) || 1 }">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Order</p>
                            <h2
                                class="mt-2 text-[1.9rem] leading-none text-[var(--color-secondary-900)] sm:text-[2.1rem]"
                                style="font-family: 'Cormorant Garamond', serif;"
                            >
                                Add your personalized order
                            </h2>
                        </div>
                        <div class="space-y-2">
                            <label for="quantity" class="text-sm font-semibold text-[var(--color-secondary-900)]">Quantity</label>
                            <div class="flex items-center rounded-full border border-[rgba(0,48,73,0.12)] bg-white shadow-[0_12px_28px_rgba(15,46,60,0.06)]">
                                <button type="button" class="px-4 py-3 text-lg text-[var(--color-secondary-900)] transition hover:text-[var(--color-primary-900)]" @click="qty = Math.max(1, qty - 1)">−</button>
                                <input id="quantity" type="number" min="1" max="20" name="quantity" x-model="qty" class="w-16 border-0 bg-transparent px-2 py-3 text-center text-base font-semibold text-[var(--color-secondary-900)] focus:outline-none focus:ring-0">
                                <button type="button" class="px-4 py-3 text-lg text-[var(--color-secondary-900)] transition hover:text-[var(--color-primary-900)]" @click="qty = Math.min(20, qty + 1)">+</button>
                            </div>
                        </div>
                    </div>

                    @error('quantity')
                        <p class="mt-3 text-sm font-medium text-[var(--color-danger)]">{{ $message }}</p>
                    @enderror
                    @error('cart')
                        <p class="mt-3 text-sm font-medium text-[var(--color-danger)]">{{ $message }}</p>
                    @enderror

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <button type="submit" class="button-primary w-full sm:flex-1">Add personalized order</button>
                        <button type="submit" name="buy_now" value="1" class="button-secondary w-full sm:flex-1">Buy now</button>
                    </div>

                    <div class="mt-5 grid gap-3 text-sm text-[var(--color-secondary-900)] sm:grid-cols-3">
                        <div class="rounded-[var(--radius-xl)] border border-[rgba(0,48,73,0.08)] bg-white/80 px-4 py-4">Proof sent before final production</div>
                        <div class="rounded-[var(--radius-xl)] border border-[rgba(0,48,73,0.08)] bg-white/80 px-4 py-4">Secure checkout</div>
                        <div class="rounded-[var(--radius-xl)] border border-[rgba(0,48,73,0.08)] bg-white/80 px-4 py-4">Carefully packaged</div>
                    </div>
                </section>
            </form>
        </aside>

        <div class="space-y-8 lg:col-span-2">
            <section class="surface-card-featured grid gap-8 p-8 lg:grid-cols-[1.05fr_0.95fr] lg:p-10">
                <div>
                    <p class="eyebrow">Product story</p>
                    <h2
                        class="mt-5 text-[2.4rem] leading-none text-[var(--color-secondary-900)] sm:text-[3.1rem]"
                        style="font-family: 'Cormorant Garamond', serif;"
                    >
                        A ceremonial keepsake designed to feel worthy of display.
                    </h2>
                    <p class="mt-5 max-w-3xl text-base leading-8 text-[var(--color-text-soft)]">{{ $storyDescription }}</p>
                </div>

                <div class="grid gap-4">
                    <div class="rounded-[var(--radius-2xl)] border border-[rgba(120,0,0,0.1)] bg-white/80 p-5">
                        <p class="text-[0.68rem] uppercase tracking-[0.22em] text-[var(--color-primary-900)]">Editorial finish</p>
                        <p class="mt-3 text-sm leading-7 text-[var(--color-secondary-900)]">Warm ivory tones, refined typography, and balanced spacing give the certificate a premium, ceremonial character rather than a generic form-fill look.</p>
                    </div>
                    <div class="rounded-[var(--radius-2xl)] border border-[rgba(0,48,73,0.08)] bg-white/80 p-5">
                        <p class="text-[0.68rem] uppercase tracking-[0.22em] text-[var(--color-secondary-900)]">Made for proofing</p>
                        <p class="mt-3 text-sm leading-7 text-[var(--color-secondary-900)]">Live personalization, font presets, and perspective scene previews help you review the certificate in both flat and styled presentation contexts before production.</p>
                    </div>
                </div>
            </section>

            <div class="grid gap-8 xl:grid-cols-[0.9fr_1.1fr]">
                <section class="surface-card p-8">
                    <p class="eyebrow">What’s included</p>
                    <h2
                        class="mt-5 text-[2.2rem] leading-none text-[var(--color-secondary-900)]"
                        style="font-family: 'Cormorant Garamond', serif;"
                    >
                        Everything needed for a polished personalized proof.
                    </h2>
                    <div class="mt-6 space-y-3">
                        @foreach ($includedItems as $item)
                            <div class="rounded-[var(--radius-xl)] border border-[rgba(0,48,73,0.08)] bg-white/80 px-4 py-4 text-sm leading-7 text-[var(--color-secondary-900)]">
                                {{ $item }}
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="surface-card p-8">
                    <p class="eyebrow">How it works</p>
                    <h2
                        class="mt-5 text-[2.2rem] leading-none text-[var(--color-secondary-900)]"
                        style="font-family: 'Cormorant Garamond', serif;"
                    >
                        A calm three-step ordering flow.
                    </h2>
                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <article class="rounded-[var(--radius-2xl)] border border-[rgba(120,0,0,0.08)] bg-[rgba(253,240,213,0.36)] p-5">
                            <p class="text-[0.68rem] uppercase tracking-[0.22em] text-[var(--color-primary-900)]">Step 1</p>
                            <h3 class="mt-3 text-lg font-semibold text-[var(--color-secondary-900)]">Enter your details</h3>
                            <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Add the bride name, groom name, Nikah date, venue, and any supporting notes directly into the structured fields.</p>
                        </article>
                        <article class="rounded-[var(--radius-2xl)] border border-[rgba(0,48,73,0.08)] bg-white/80 p-5">
                            <p class="text-[0.68rem] uppercase tracking-[0.22em] text-[var(--color-primary-900)]">Step 2</p>
                            <h3 class="mt-3 text-lg font-semibold text-[var(--color-secondary-900)]">Review your live proof</h3>
                            <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Switch between the flat certificate and curated mockup scenes while testing different font presets.</p>
                        </article>
                        <article class="rounded-[var(--radius-2xl)] border border-[rgba(0,48,73,0.08)] bg-white/80 p-5">
                            <p class="text-[0.68rem] uppercase tracking-[0.22em] text-[var(--color-primary-900)]">Step 3</p>
                            <h3 class="mt-3 text-lg font-semibold text-[var(--color-secondary-900)]">Approve before production</h3>
                            <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">We send a proof for review so the final printed piece moves into production only after approval.</p>
                        </article>
                    </div>
                </section>
            </div>

            <div class="grid gap-8 xl:grid-cols-[0.9fr_1.1fr]">
                <section class="surface-card-soft p-8">
                    <p class="eyebrow">Delivery & production</p>
                    <h2
                        class="mt-5 text-[2.2rem] leading-none text-[var(--color-secondary-900)]"
                        style="font-family: 'Cormorant Garamond', serif;"
                    >
                        Timeline and fulfillment details.
                    </h2>
                    <div class="mt-6 space-y-4">
                        @foreach ($productionNotes as $note)
                            <div class="rounded-[var(--radius-2xl)] border border-[rgba(0,48,73,0.08)] bg-white/75 p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[0.68rem] uppercase tracking-[0.22em] text-[var(--color-text-soft)]">{{ $note['label'] }}</p>
                                        <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $note['value'] }}</p>
                                    </div>
                                </div>
                                <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">{{ $note['copy'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="surface-card p-8">
                    <p class="eyebrow">FAQ</p>
                    <h2
                        class="mt-5 text-[2.2rem] leading-none text-[var(--color-secondary-900)]"
                        style="font-family: 'Cormorant Garamond', serif;"
                    >
                        Common questions before you order.
                    </h2>
                    <div class="mt-6 space-y-4">
                        @foreach ($faqItems as $item)
                            <details class="rounded-[var(--radius-2xl)] border border-[rgba(0,48,73,0.08)] bg-white/80 p-5" @if ($loop->first) open @endif>
                                <summary class="cursor-pointer text-base font-semibold text-[var(--color-secondary-900)]">{{ $item['question'] }}</summary>
                                <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">{{ $item['answer'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
            </div>

            @if ($product->relatedProducts->isNotEmpty())
                <section class="surface-card-featured p-8 lg:p-10">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="eyebrow">Complementary keepsakes</p>
                            <h2
                                class="mt-5 text-[2.2rem] leading-none text-[var(--color-secondary-900)]"
                                style="font-family: 'Cormorant Garamond', serif;"
                            >
                                Pair the certificate with matching ceremonial pieces.
                            </h2>
                            <p class="mt-3 max-w-3xl text-sm leading-7 text-[var(--color-text-soft)]">Thoughtful add-ons for signing, gifting, or building a fuller Nikah presentation set.</p>
                        </div>
                        <a href="{{ route('shop.index') }}" class="button-ghost">Explore more</a>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @foreach ($product->relatedProducts->take(4) as $relatedProduct)
                            <x-storefront.listing-card :product="$relatedProduct" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($recentlyViewed->isNotEmpty())
                <section class="surface-card p-8">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="eyebrow">Recently viewed</p>
                            <h2
                                class="mt-5 text-[2.2rem] leading-none text-[var(--color-secondary-900)]"
                                style="font-family: 'Cormorant Garamond', serif;"
                            >
                                Continue building your keepsake set.
                            </h2>
                        </div>
                        <a href="{{ route('shop.index') }}" class="text-sm font-semibold text-[var(--color-primary-900)]">Continue browsing</a>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @foreach ($recentlyViewed as $recentProduct)
                            <x-storefront.listing-card :product="$recentProduct" />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <div class="fixed inset-x-3 bottom-3 z-30 lg:hidden">
            <div class="rounded-[28px] border border-[rgba(0,48,73,0.1)] bg-[rgba(255,251,245,0.96)] px-4 py-3 shadow-[0_18px_45px_rgba(15,46,60,0.16)] backdrop-blur">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[0.68rem] uppercase tracking-[0.2em] text-[var(--color-text-soft)]">Personalized order</p>
                        <p class="text-lg font-semibold text-[var(--color-secondary-900)]">BDT {{ number_format((float) $product->price, 0) }}</p>
                    </div>
                    <a href="#order-panel" class="button-primary !px-5 !py-3">Order now</a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.product-detail>
