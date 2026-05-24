@php
    $mockupItems = $mockups instanceof \Illuminate\Support\Collection ? $mockups->values() : collect($mockups ?? [])->values();
    $generalImages = $generalImages instanceof \Illuminate\Support\Collection ? $generalImages->values() : collect($generalImages ?? [])->values();
    $flatThumb = $template?->previewArtworkUrl() ?: $template?->baseArtworkUrl() ?: $product->featured_image_url;
    $showFlatPreview = $product->is_customizable;
@endphp

<section class="lg:self-stretch">
    <div class="space-y-4 lg:sticky lg:top-[88px]">
    <div class="surface-product overflow-hidden p-4 sm:p-5">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-[var(--text-muted)]">Preview gallery</p>
                <p class="mt-1 text-sm font-medium text-[var(--text-main)]" x-text="currentPreviewTitle">
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
            <div class="absolute left-3 top-3 z-10" x-cloak x-show="hasInput" x-transition.opacity.duration.200ms>
                <span class="inline-flex items-center rounded-full border border-[var(--border-soft)] bg-white/95 px-3 py-1 text-[11px] font-medium text-[var(--accent-primary)] shadow-sm">
                    Live preview
                </span>
            </div>

            <div class="absolute right-3 top-3 z-10 hidden rounded-full bg-white/90 px-3 py-1 text-[11px] text-[var(--text-muted)] shadow-sm lg:block">
                ⊕ Hover to zoom
            </div>

            <div class="aspect-[4/5] w-full max-h-[58vh] lg:max-h-[500px]">
                @if ($product->is_customizable)
                    <canvas
                        id="nikah-preview-canvas"
                        x-ref="previewCanvas"
                        aria-label="Certificate preview"
                        class="block h-full w-full origin-center transform-gpu transition-transform duration-300 ease-out"
                    ></canvas>
                @else
                    <img
                        :src="activeGeneralImage?.url || @js($generalImages->first()['url'] ?? $product->featured_image_url)"
                        :alt="activeGeneralImage?.alt || @js($generalImages->first()['alt'] ?? $product->name)"
                        class="block h-full w-full object-cover transition duration-200 ease-out"
                    >
                @endif
            </div>

            <template x-if="{{ $product->is_customizable ? 'previewCount > 1' : 'generalImageCount > 1' }}">
                <div>
                    <button
                        type="button"
                        class="absolute left-3 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-lg bg-white/75 text-3xl text-[var(--text-main)] shadow-sm backdrop-blur transition duration-200 ease-out hover:bg-white"
                        @click.stop="previousPreview()"
                        aria-label="Previous preview"
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-lg bg-white/75 text-3xl text-[var(--text-main)] shadow-sm backdrop-blur transition duration-200 ease-out hover:bg-white"
                        @click.stop="nextPreview()"
                        aria-label="Next preview"
                    >
                        ›
                    </button>
                </div>
            </template>
        </div>

        <div class="mt-4 p-1 flex snap-x snap-mandatory gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
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
