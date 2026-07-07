@props(['product'])

<article class="product-card-lux group">
    @php
        $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
        $defaultMockup = $product->is_customizable ? $product->defaultPersonalizationMockup() : null;
        $template = $product->is_customizable
            ? ($product->relationLoaded('personalizationTemplate') ? $product->personalizationTemplate : $product->personalizationTemplate()->first())
            : null;
        $mockupMap = $defaultMockup?->map ? \App\Support\MockupZoneNormalizer::toImageSpace($defaultMockup, $defaultMockup->map) : null;
        $flatArtwork = $template?->previewArtworkUrl() ?: $template?->baseArtworkUrl() ?: $template?->thumbnailArtworkUrl();
        $canLayerMockup = $product->is_customizable
            && filled($defaultMockup?->base_image_url)
            && filled($flatArtwork)
            && is_array($mockupMap);
        $hasProductImage = filled($product->storefront_preview_image_url);
        $cardImage = $hasProductImage ? $product->storefront_preview_image_url : asset('images/logo/Azraq.svg');
        $cardAlt = $primaryImage?->label ?: $product->name;
    @endphp
    <a href="{{ route('products.show', $product) }}" class="block overflow-hidden rounded-t-[var(--radius-2xl)]" aria-label="View {{ $product->name }}">
        @if ($hasProductImage)
            <img
                src="{{ $cardImage }}"
                alt="{{ $cardAlt }}"
                class="aspect-[4/5] sm:aspect-[4/3] w-full object-cover transition duration-500 ease-out group-hover:scale-105"
                loading="lazy"
                decoding="async"
            >
        @elseif ($canLayerMockup)
            <div
                class="product-card-lux__mockup-stage relative aspect-[4/5] w-full overflow-hidden bg-[rgba(253,240,213,0.50)] transition duration-500 ease-out group-hover:scale-105 sm:aspect-[4/3]"
                data-card-mockup-stage
                data-map='@json($mockupMap)'
                data-image-width="{{ (int) ($defaultMockup->image_width ?: 1600) }}"
                data-image-height="{{ (int) ($defaultMockup->image_height ?: 1600) }}"
            >
                <img
                    src="{{ $defaultMockup->base_image_url }}"
                    alt="{{ $cardAlt }}"
                    class="product-card-lux__mockup-base absolute inset-0 h-full w-full object-cover"
                    loading="lazy"
                    decoding="async"
                >
                <img
                    src="{{ $flatArtwork }}"
                    alt=""
                    class="product-card-lux__mockup-template absolute left-0 top-0 h-full w-full object-fill"
                    loading="lazy"
                    decoding="async"
                    aria-hidden="true"
                    data-card-mockup-template
                >
            </div>
        @else
            <img
                src="{{ $cardImage }}"
                alt="{{ $cardAlt }}"
                @class([
                    'aspect-[4/5] sm:aspect-[4/3] w-full transition duration-500 ease-out group-hover:scale-105',
                    'object-contain bg-[rgba(253,240,213,0.50)] p-12 sm:p-14',
                ])
                loading="lazy"
                decoding="async"
            >
        @endif
    </a>

    <div class="product-card-lux__body">
        <p class="product-card-lux__kicker">{{ $product->category?->name ?: $product->type?->label() }}</p>
        <h3 class="product-card-lux__title">
            <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
        </h3>
        <p class="product-card-lux__excerpt">{{ \Illuminate\Support\Str::limit($product->excerpt ?: strip_tags($product->description), 90) }}</p>
    </div>

    <div class="product-card-lux__footer">
        <div class="product-card-lux__price-block">
            <p class="product-card-lux__price">BDT {{ number_format((float) $product->price, 0) }}</p>
            @if ($product->compare_at_price)
                <p class="product-card-lux__compare">BDT {{ number_format((float) $product->compare_at_price, 0) }}</p>
            @endif
        </div>
        <a href="{{ route('products.show', $product) }}" class="product-card-lux__cta" aria-label="View {{ $product->name }}">View →</a>
    </div>
</article>
