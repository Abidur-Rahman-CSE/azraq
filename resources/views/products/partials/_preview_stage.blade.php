@php
    $mockupItems = $mockups instanceof \Illuminate\Support\Collection ? $mockups->values() : collect($mockups ?? [])->values();
    $generalImages = $generalImages instanceof \Illuminate\Support\Collection ? $generalImages->values() : collect($generalImages ?? [])->values();
    $flatThumb = $template?->thumbnailArtworkUrl() ?: $template?->previewArtworkUrl() ?: $template?->baseArtworkUrl() ?: $product->featured_image_url;
    $showFlatPreview = $product->is_customizable;
@endphp

<section class="min-w-0 lg:sticky lg:top-[88px] lg:self-start">
    <div class="min-w-0 space-y-4">
    <div class="surface-product max-w-full overflow-hidden p-2.5 sm:p-5">
        <div class="mb-4 flex min-w-0 items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-[var(--text-muted)]">Preview gallery</p>
                <p class="mt-1 truncate text-sm font-medium text-[var(--text-main)]" x-text="currentPreviewTitle">
                    {{ $product->is_customizable ? ($mockupItems->first()['name'] ?? 'Template preview') : ($generalImages->first()['label'] ?? $product->name) }}
                </p>
            </div>
            @if ($product->is_customizable && $mockupItems->isNotEmpty())
                <p class="hidden text-xs text-[var(--text-muted)] lg:block">⊕ Hover to zoom</p>
            @endif
        </div>

        <div
            id="preview-stage"
            x-ref="previewStage"
            class="group relative overflow-hidden rounded-xl border border-[var(--border-soft)] bg-[var(--bg-section-soft)]"
        >
            <div class="absolute right-3 top-3 z-10 hidden rounded-full bg-white/90 px-3 py-1 text-[11px] text-[var(--text-muted)] shadow-sm lg:block">
                ⊕ Hover to zoom
            </div>

            <div
                class="relative mx-auto flex aspect-[4/5] w-full max-w-[320px] items-center justify-center overflow-hidden bg-[var(--bg-section-soft)] sm:max-w-[420px] lg:max-h-[500px] lg:max-w-none"
                style="aspect-ratio: 4 / 5;"
            >
                @if ($product->is_customizable)
                    <img
                        :src="activePreviewFillerUrl"
                        alt=""
                        aria-hidden="true"
                        class="pointer-events-none absolute inset-0 h-full w-full scale-110 object-cover opacity-45 blur-2xl"
                        draggable="false"
                        data-protected-image
                        x-show="activePreviewFillerUrl"
                    >
                    <div class="absolute inset-0 bg-[rgba(255,250,242,0.22)]" aria-hidden="true"></div>
                    <canvas
                        id="nikah-preview-canvas"
                        x-ref="previewCanvas"
                        aria-label="Certificate preview"
                        class="relative z-10 block h-full w-full origin-center object-contain transform-gpu transition-transform duration-300 ease-out"
                        data-protected-image
                    ></canvas>
                @else
                    <img
                        :src="activeGeneralImage?.url || @js($generalImages->first()['url'] ?? $product->featured_image_url)"
                        alt=""
                        aria-hidden="true"
                        class="pointer-events-none absolute inset-0 h-full w-full scale-110 object-cover opacity-60 blur-2xl"
                        draggable="false"
                        data-protected-image
                    >
                    <div class="absolute inset-0 bg-[rgba(255,250,242,0.28)]" aria-hidden="true"></div>
                    <img
                        x-ref="generalPreviewImage"
                        :src="activeGeneralImage?.url || @js($generalImages->first()['url'] ?? $product->featured_image_url)"
                        :alt="activeGeneralImage?.alt || @js($generalImages->first()['alt'] ?? $product->name)"
                        class="relative block h-full w-full object-contain transition duration-200 ease-out"
                        draggable="false"
                        data-protected-image
                    >
                @endif
            </div>

            @if ($product->is_customizable)
                <div
                    class="pointer-events-none absolute inset-0 z-20 flex items-center justify-center bg-white/45 backdrop-blur-[1px] transition-opacity duration-150"
                    x-cloak
                    x-show="previewBusy"
                    x-transition.opacity.duration.150ms
                    aria-hidden="true"
                >
                    <span class="h-8 w-8 rounded-full border-2 border-[var(--border-soft)] border-t-[var(--accent-primary)] motion-safe:animate-spin"></span>
                </div>
            @endif

            <template x-if="{{ $product->is_customizable ? 'previewCount > 1' : 'generalImageCount > 1' }}">
                <div>
                    <button
                        type="button"
                        class="absolute left-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-lg bg-white/80 text-2xl text-[var(--text-main)] shadow-sm backdrop-blur transition duration-200 ease-out hover:bg-white sm:left-3 sm:h-12 sm:w-12 sm:text-3xl"
                        @click.stop="previousPreview()"
                        aria-label="Previous preview"
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        class="absolute right-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-lg bg-white/80 text-2xl text-[var(--text-main)] shadow-sm backdrop-blur transition duration-200 ease-out hover:bg-white sm:right-3 sm:h-12 sm:w-12 sm:text-3xl"
                        @click.stop="nextPreview()"
                        aria-label="Next preview"
                    >
                        ›
                    </button>
                </div>
            </template>
        </div>

        <div class="mt-4 flex max-w-full snap-x snap-mandatory gap-2 overflow-x-auto p-1 pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            @if ($product->is_customizable && $showFlatPreview)
                <button
                    type="button"
                    class="w-[72px] shrink-0 snap-start"
                    @click="selectThumb(0)"
                    aria-label="Select template preview"
                >
                    <div
                        class="overflow-hidden rounded-lg border-2 bg-[var(--bg-section-soft)] transition-all duration-200 ease-out"
                        :class="activeThumb === 0 ? 'scale-105 border-[var(--accent-primary)]' : 'border-transparent'"
                    >
                        <div class="flex h-[64px] w-[64px] items-center justify-center rounded-md bg-white/70">
                            <img :src="previewThumbs.flat || @js($flatThumb)" src="{{ $flatThumb }}" alt="Template preview" class="h-full w-full rounded-md object-contain object-center">
                        </div>
                    </div>
                </button>
            @endif

            @foreach ($mockupItems as $index => $mockup)
                @php($thumbIndex = $showFlatPreview ? $index + 1 : $index)
                <button
                    type="button"
                    class="w-[72px] shrink-0 snap-start"
                    @click="selectThumb({{ $thumbIndex }})"
                    aria-label="Select {{ $mockup['name'] ?? 'Scene preview' }}"
                >
                    <div
                        class="overflow-hidden rounded-lg border-2 bg-[var(--bg-section-soft)] transition-all duration-200 ease-out"
                        :class="activeThumb === {{ $thumbIndex }} ? 'scale-105 border-[var(--accent-primary)]' : 'border-transparent'"
                    >
                        <div class="flex h-[64px] w-[64px] items-center justify-center rounded-md bg-white/70">
                            <img :src="previewThumbs['mockup-{{ $index }}'] || @js($mockup['thumbnail_url'] ?? $mockup['image_url'] ?? $flatThumb)" src="{{ $mockup['thumbnail_url'] ?? $mockup['image_url'] ?? $flatThumb }}" alt="{{ $mockup['name'] ?? 'Scene preview' }}" class="h-full w-full rounded-md object-contain object-center">
                        </div>
                    </div>
                </button>
            @endforeach

            @if (! $product->is_customizable)
                @foreach ($generalImages as $index => $image)
                    <button
                        type="button"
                        class="w-[72px] shrink-0 snap-start"
                        @click="selectImage({{ $index }})"
                        aria-label="Select {{ $image['label'] ?? $product->name }} image"
                    >
                        <div
                        class="overflow-hidden rounded-lg border-2 bg-[var(--bg-section-soft)] p-1 transition-all duration-200 ease-out"
                        :class="activeImage === {{ $index }} ? 'scale-105 border-[var(--accent-primary)]' : 'border-transparent'"
                        >
                            <div class="flex h-[64px] w-[64px] items-center justify-center rounded-md bg-white/70">
                                <img src="{{ $image['thumb'] ?? $image['url'] }}" alt="{{ $image['alt'] ?? $product->name }}" class="h-full w-full rounded-md object-contain object-center">
                            </div>
                        </div>
                    </button>
                @endforeach
            @endif
        </div>

        @if ($product->is_customizable)
            <p class="mt-2 text-center text-xs italic text-[var(--text-muted)]">Your names and date update live in the preview</p>
        @endif
    </div>
    </div>
</section>
