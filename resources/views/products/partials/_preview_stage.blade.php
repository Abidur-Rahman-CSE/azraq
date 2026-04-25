@php
    $mockupItems = $mockups instanceof \Illuminate\Support\Collection ? $mockups->values() : collect($mockups ?? [])->values();
    $generalImages = $generalImages instanceof \Illuminate\Support\Collection ? $generalImages->values() : collect($generalImages ?? [])->values();
    $flatThumb = $template?->preview_image_url ?: $template?->base_template_url ?: $product->featured_image_url;
    $showFlatPreview = $product->is_customizable;
@endphp

<section class="space-y-4 lg:self-start">
    <div class="overflow-hidden rounded-xl border border-[#E8E3DC] bg-white p-4 shadow-[0_2px_20px_rgba(0,0,0,0.05)] sm:p-5">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-[#8C7F74]">Preview gallery</p>
                <p class="mt-1 text-sm font-medium text-[#2C2C3E]" x-text="currentPreviewTitle">
                    {{ $product->is_customizable ? ($mockupItems->first()['name'] ?? 'Template preview') : ($generalImages->first()['label'] ?? $product->name) }}
                </p>
            </div>
            @if ($product->is_customizable && $mockupItems->isNotEmpty())
                <p class="hidden text-xs text-[#8C7F74] lg:block">⊕ Hover to zoom</p>
            @endif
        </div>

        <div
            id="preview-stage"
            x-ref="previewStage"
            class="group relative overflow-hidden rounded-xl border border-[#E8E3DC] bg-[#F5F2EC]"
        >
            <div class="absolute left-3 top-3 z-10" x-cloak x-show="hasInput" x-transition.opacity.duration.200ms>
                <span class="inline-flex items-center rounded-full border border-[#E8E3DC] bg-white/95 px-3 py-1 text-[11px] font-medium text-[#8B2635] shadow-sm">
                    Live preview
                </span>
            </div>

            <div class="absolute right-3 top-3 z-10 hidden rounded-full bg-white/90 px-3 py-1 text-[11px] text-[#8C7F74] shadow-sm lg:block">
                ⊕ Hover to zoom
            </div>

            <div class="aspect-[4/5] w-full">
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
                        class="absolute left-3 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-lg bg-white/75 text-3xl text-[#3D3730] shadow-sm backdrop-blur transition duration-200 ease-out hover:bg-white"
                        @click="previousPreview()"
                        aria-label="Previous preview"
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-lg bg-white/75 text-3xl text-[#3D3730] shadow-sm backdrop-blur transition duration-200 ease-out hover:bg-white"
                        @click="nextPreview()"
                        aria-label="Next preview"
                    >
                        ›
                    </button>
                </div>
            </template>
        </div>

        <div class="mt-4 flex snap-x snap-mandatory gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            @if ($product->is_customizable && $showFlatPreview)
                <button
                    type="button"
                    class="w-[72px] shrink-0 snap-start"
                    @click="selectThumb(0)"
                    aria-label="Select template preview"
                >
                    <div
                        class="overflow-hidden rounded-lg border-2 bg-[#F5F2EC] transition-all duration-200 ease-out"
                        :class="activeThumb === 0 ? 'scale-105 border-[#C4A882]' : 'border-transparent'"
                    >
                        <img src="{{ $flatThumb }}" alt="Template preview" class="h-[72px] w-[72px] object-cover">
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
                        class="overflow-hidden rounded-lg border-2 bg-[#F5F2EC] transition-all duration-200 ease-out"
                        :class="activeThumb === {{ $thumbIndex }} ? 'scale-105 border-[#C4A882]' : 'border-transparent'"
                    >
                        <img src="{{ $mockup['thumbnail_url'] ?? $mockup['image_url'] ?? $flatThumb }}" alt="{{ $mockup['name'] ?? 'Scene preview' }}" class="h-[72px] w-[72px] object-cover">
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
                            class="overflow-hidden rounded-lg border-2 bg-[#F5F2EC] transition-all duration-200 ease-out"
                            :class="activeImage === {{ $index }} ? 'scale-105 border-[#C4A882]' : 'border-transparent'"
                        >
                            <img src="{{ $image['thumb'] ?? $image['url'] }}" alt="{{ $image['alt'] ?? $product->name }}" class="h-[72px] w-[72px] object-cover">
                        </div>
                    </button>
                @endforeach
            @endif
        </div>

        @if ($product->is_customizable)
            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                @if ($showFlatPreview)
                    <p class="text-[10px] text-[#5F5C58] sm:col-span-2 lg:col-span-1">Template preview</p>
                @endif
                @foreach ($mockupItems as $index => $mockup)
                    <p class="text-[10px] text-[#5F5C58] {{ $showFlatPreview && $index > 2 ? 'hidden lg:block' : '' }}">{{ $mockup['name'] ?? 'Scene preview' }}</p>
                @endforeach
            </div>

            <p class="mt-2 text-center text-xs italic text-[#8C7F74]">Your names and date update live in the preview</p>
        @endif
    </div>
</section>
