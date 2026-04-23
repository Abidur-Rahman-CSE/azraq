@php
    $firstGeneralImage = $generalImages->first();
    $mockupItems = $mockups instanceof \Illuminate\Support\Collection ? $mockups : collect($mockups ?? []);
    $showFlatPreview = (bool) ($showFlatPreview ?? false);
    $customPreviewCount = $mockupItems->count() + ($showFlatPreview ? 1 : 0);
@endphp

<section class="space-y-5 lg:sticky lg:top-28 lg:self-start">
    <x-storefront.product-breadcrumbs :product="$product" />

    <div class="rounded-[1.75rem] border border-[#E8E3DC] bg-[linear-gradient(180deg,#FFFFFF_0%,#FCFAF6_100%)] p-4 shadow-[0_2px_16px_rgba(0,0,0,0.06)] sm:p-6">
        <div class="mb-5 flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#C4A882]">{{ $product->is_customizable ? 'Preview gallery' : 'Product gallery' }}</p>
                <h2 class="mt-2 font-serif text-xl font-semibold text-[#2C2C3E]">Active frame</h2>
                <p class="mt-1 text-sm text-[#8C7F74]" x-text="currentPreviewTitle">{{ $product->is_customizable ? ($mockupItems->first()['name'] ?? 'Selected mockup preview') : 'A closer look at the piece' }}</p>
            </div>
            @if ($product->is_customizable)
                <p class="max-w-[13rem] text-right text-xs leading-5 text-[#8C7F74]">Nikahnama will be composited at the saved zone position.</p>
            @endif
        </div>

        <div class="rounded-[1.4rem] border border-[rgba(196,168,130,0.3)] bg-[#FBF8F3] p-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.9)] sm:p-5">
            <div class="relative overflow-hidden rounded-[1.2rem] border border-[#E8E3DC] bg-[#F5F2EC] shadow-[0_10px_28px_rgba(44,44,62,0.08)]">
                @if ($product->is_customizable)
                    <div class="absolute left-4 top-4 z-10">
                        <span
                            class="rounded-full border border-[rgba(139,38,53,0.12)] bg-[#FAF8F5] px-3 py-1 text-xs font-semibold text-[#8B2635] shadow-sm"
                            x-show="hasInput"
                            x-transition.opacity.duration.200ms
                        >
                            Live preview
                        </span>
                    </div>

                    <div class="aspect-[4/5] w-full">
                        <canvas id="nikah-preview-canvas" aria-label="Certificate preview" class="block h-full w-full"></canvas>
                    </div>
                @else
                    <div class="aspect-[4/5] w-full">
                        <img
                            :src="activeGeneralImage?.url || @js(data_get($firstGeneralImage, 'url'))"
                            :alt="activeGeneralImage?.alt || @js(data_get($firstGeneralImage, 'alt', $product->name))"
                            class="h-full w-full object-cover transition duration-200 ease-out"
                        >
                    </div>
                @endif

                @if ($product->is_customizable ? ($customPreviewCount > 1) : ($generalImages->count() > 1))
                    <button
                        type="button"
                        class="absolute left-4 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-sm bg-[rgba(175,175,175,0.72)] text-3xl text-white transition duration-200 ease-out hover:bg-[rgba(120,120,120,0.88)]"
                        @click="previousPreview()"
                        aria-label="Show previous preview"
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        class="absolute right-4 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-sm bg-[rgba(175,175,175,0.72)] text-3xl text-white transition duration-200 ease-out hover:bg-[rgba(120,120,120,0.88)]"
                        @click="nextPreview()"
                        aria-label="Show next preview"
                    >
                        ›
                    </button>
                @endif

                <div class="absolute bottom-4 left-4 rounded-full bg-[rgba(255,255,255,0.88)] px-3 py-1.5 text-xs font-semibold tracking-[0.18em] text-[#2C2C3E] shadow-sm">
                    <span x-text="previewPositionLabel">{{ $product->is_customizable ? '1 / '.max(1, $customPreviewCount) : '1 / '.$generalImages->count() }}</span>
                </div>
            </div>
        </div>

        <div class="mt-3 flex items-center gap-4 text-sm text-[#2C2C3E]">
            @if ($product->is_customizable)
                @php($previewCount = $customPreviewCount)
                @for ($previewIndex = 0; $previewIndex < $previewCount; $previewIndex++)
                    <button
                        type="button"
                        class="font-medium transition duration-200 ease-out"
                        :class="activePreviewIndex === {{ $previewIndex }} ? 'text-[#2C2C3E]' : 'text-[#8C7F74]'"
                        @click="selectPreview({{ $previewIndex }})"
                    >
                        {{ $previewIndex + 1 }}
                    </button>
                @endfor
            @endif
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            @if ($product->is_customizable)
                @if ($showFlatPreview)
                    <article class="rounded-xl border border-[#E8E3DC] bg-white p-2 transition duration-200 ease-out" :class="activePreviewIndex === 0 ? 'border-[#C4A882] shadow-[0_12px_28px_rgba(210,138,31,0.14)]' : ''">
                        <button
                            type="button"
                            class="block w-full overflow-hidden rounded-[10px] bg-[#F5F2EC]"
                            @click="selectPreview(0)"
                            aria-label="Select flat certificate preview"
                        >
                            <img src="{{ $template?->preview_image_url ?: $template?->base_template_url }}" alt="Flat certificate preview" class="aspect-[4/3] w-full object-cover">
                        </button>
                        <div class="mt-2 grid gap-1">
                            <strong class="text-sm text-[#2C2C3E]">Flat certificate preview</strong>
                            <span class="text-xs text-[#8C7F74]">Template flat proof · Zone source</span>
                            <button type="button" class="mt-1 w-fit rounded-full border border-[#E8E3DC] bg-white px-3 py-1.5 text-xs font-semibold text-[#2C2C3E]" @click="selectPreview(0)">
                                <span x-text="activePreviewIndex === 0 ? 'Active frame' : 'Make active'">Make active</span>
                            </button>
                        </div>
                    </article>
                @endif

                @foreach ($mockupItems as $index => $mockup)
                    @php($previewIndex = $index + ($showFlatPreview ? 1 : 0))
                    <article class="rounded-xl border border-[#E8E3DC] bg-white p-2 transition duration-200 ease-out" :class="activePreviewIndex === {{ $previewIndex }} ? 'border-[#C4A882] shadow-[0_12px_28px_rgba(210,138,31,0.14)]' : ''">
                        <button
                            type="button"
                            class="block w-full overflow-hidden rounded-[10px] bg-[#F5F2EC]"
                            @click="selectPreview({{ $previewIndex }})"
                            aria-label="Select {{ $mockup['name'] ?? 'Scene preview' }} preview"
                        >
                            <img src="{{ $mockup['thumbnail_url'] ?? null }}" alt="{{ $mockup['name'] ?? 'Scene preview' }}" class="aspect-[4/3] w-full object-cover">
                        </button>
                        <div class="mt-2 grid gap-1">
                            <strong class="text-sm text-[#2C2C3E]">{{ $mockup['name'] ?? 'Scene preview' }}</strong>
                            <span class="text-xs text-[#8C7F74]">
                                {{ filled($mockup['template_name'] ?? null) ? $mockup['template_name'].' · ' : 'Reusable scene · ' }}
                                {{ filled($mockup['map'] ?? null) ? 'Zone mapped' : 'Zone pending' }}
                            </span>
                            <div class="mt-1 flex flex-wrap gap-2">
                                <button type="button" class="rounded-full border border-[#E8E3DC] bg-white px-3 py-1.5 text-xs font-semibold text-[#2C2C3E]" @click="selectPreview({{ $previewIndex }})">
                                    <span x-text="activePreviewIndex === {{ $previewIndex }} ? 'Active frame' : 'Make active'">Make active</span>
                                </button>
                                @if ($mockup['is_default'] ?? false)
                                    <span class="rounded-full border border-[rgba(47,111,228,0.22)] bg-[rgba(47,111,228,0.08)] px-3 py-1.5 text-xs font-semibold text-[#2F6FE4]">Selected</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach

                @if ($customPreviewCount === 0)
                    <article class="rounded-xl border border-dashed border-[#E8E3DC] bg-[#FAF8F5] p-5 text-sm text-[#8C7F74]">
                        No storefront mockup is selected yet. Add mockups from Admin > Advanced customization to show them here.
                    </article>
                @endif
            @else
                @foreach ($generalImages as $index => $image)
                    <button
                        type="button"
                        class="w-[120px] flex-none overflow-hidden rounded-sm border border-[#E8E3DC] bg-white transition duration-200 ease-out"
                        :class="activeImage === {{ $index }} ? 'ring-2 ring-[#C4A882]' : ''"
                        @click="selectImage({{ $index }})"
                        aria-label="Select {{ $image['label'] }} image"
                    >
                        <img src="{{ $image['thumb'] }}" alt="{{ $image['alt'] }}" class="aspect-[5/4] w-full object-cover">
                    </button>
                @endforeach
            @endif
        </div>

        @if ($product->is_customizable)
            <div class="mt-4 rounded-2xl border border-dashed border-[rgba(196,168,130,0.5)] bg-[rgba(250,248,245,0.9)] px-4 py-3">
                <p class="text-sm italic text-[#8C7F74]">Your names update live and stay consistent across every preview image.</p>
            </div>
        @endif
    </div>

    @if ($recentlyViewed->isNotEmpty())
        <div class="rounded-[1.75rem] border border-[#E8E3DC] bg-white p-5 shadow-[0_2px_16px_rgba(0,0,0,0.06)] sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-serif text-xl font-semibold text-[#2C2C3E]">Recently viewed</h2>
                <a href="{{ route('shop.index') }}" class="text-sm font-medium text-[#8B2635]">Continue browsing</a>
            </div>

            <div class="mt-4 grid gap-4">
                @foreach ($recentlyViewed as $recentProduct)
                    <x-storefront.listing-card :product="$recentProduct" />
                @endforeach
            </div>
        </div>
    @endif
</section>
