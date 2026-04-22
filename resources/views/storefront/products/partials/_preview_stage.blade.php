@php($firstGeneralImage = $generalImages->first())

<section class="space-y-4 lg:sticky lg:top-28 lg:self-start">
    <x-storefront.product-breadcrumbs :product="$product" />

    <div class="rounded-[1.5rem] border border-[#E8E3DC] bg-white p-4 shadow-[0_2px_16px_rgba(0,0,0,0.06)] sm:p-5">
        @if ($product->is_customizable)
            <p class="mb-4 text-xs font-semibold uppercase tracking-[0.18em] text-[#C4A882]">Preview gallery</p>
        @endif

        <div class="relative overflow-hidden rounded-[1.25rem] border border-[#E8E3DC] bg-[#F5F2EC]">
            @if ($product->is_customizable)
                <div class="absolute left-4 top-4 z-10">
                    <span
                        class="rounded-full bg-[#FAF8F5] px-3 py-1 text-xs font-semibold text-[#8B2635]"
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
        </div>

        <div class="mt-4 flex gap-3 overflow-x-auto pb-1">
            @if ($product->is_customizable)
                <button
                    type="button"
                    class="w-[72px] flex-none rounded-md border-2 bg-[#FAF8F5] p-1 transition duration-200 ease-out"
                    :class="mode === 'flat' ? 'scale-[1.03] border-[#C4A882]' : 'border-transparent'"
                    @click="switchMode('flat')"
                    aria-label="Select flat certificate preview"
                >
                    <div class="overflow-hidden rounded-sm border border-[#E8E3DC] bg-white">
                        <img src="{{ $template?->preview_image_url ?: $template?->base_template_url }}" alt="Flat certificate preview" class="aspect-square w-full object-cover">
                    </div>
                    <p class="mt-2 text-left text-[11px] font-medium text-[#3D3730]">Flat certificate preview</p>
                </button>

                @foreach (($mockups instanceof \Illuminate\Support\Collection ? $mockups : collect($mockups ?? [])) as $index => $mockup)
                    <button
                        type="button"
                        class="w-[72px] flex-none rounded-md border-2 bg-[#FAF8F5] p-1 transition duration-200 ease-out"
                        :class="mode === 'mockup' && activeMockup === {{ $index }} ? 'scale-[1.03] border-[#C4A882]' : 'border-transparent'"
                        @click="selectMockup({{ $index }})"
                        aria-label="Select {{ $mockup['name'] ?? 'Scene preview' }} preview"
                    >
                        <div class="overflow-hidden rounded-sm border border-[#E8E3DC] bg-white">
                            <img src="{{ $mockup['thumbnail_url'] ?? null }}" alt="{{ $mockup['name'] ?? 'Scene preview' }}" class="aspect-square w-full object-cover">
                        </div>
                        <p class="mt-2 line-clamp-2 text-left text-[11px] font-medium text-[#3D3730]">{{ $mockup['name'] ?? 'Scene preview' }}</p>
                    </button>
                @endforeach
            @else
                @foreach ($generalImages as $index => $image)
                    <button
                        type="button"
                        class="w-[72px] flex-none rounded-md border-2 bg-[#FAF8F5] p-1 transition duration-200 ease-out"
                        :class="activeImage === {{ $index }} ? 'scale-[1.03] border-[#C4A882]' : 'border-transparent'"
                        @click="selectImage({{ $index }})"
                        aria-label="Select {{ $image['label'] }} image"
                    >
                        <div class="overflow-hidden rounded-sm border border-[#E8E3DC] bg-white">
                            <img src="{{ $image['thumb'] }}" alt="{{ $image['alt'] }}" class="aspect-square w-full object-cover">
                        </div>
                    </button>
                @endforeach
            @endif
        </div>

        @if ($product->is_customizable)
            <p class="mt-3 text-sm italic text-[#8C7F74]">Your names and date update live in the preview</p>
        @endif
    </div>

    @if ($recentlyViewed->isNotEmpty())
        <div class="rounded-[1.5rem] border border-[#E8E3DC] bg-white p-5 shadow-[0_2px_16px_rgba(0,0,0,0.06)]">
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
