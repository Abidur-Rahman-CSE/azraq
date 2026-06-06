@props(['product'])

<article class="product-card-lux group">
    @php($primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first())
    @php($hasProductImage = filled($product->storefront_preview_image_url))
    @php($cardImage = $hasProductImage ? $product->storefront_preview_image_url : asset('images/logo/Azraq.svg'))
    @php($cardAlt = $primaryImage?->label ?: $product->name)
    <a href="{{ route('products.show', $product) }}" class="block overflow-hidden rounded-t-[var(--radius-2xl)]" aria-label="View {{ $product->name }}">
        <img
            src="{{ $cardImage }}"
            alt="{{ $cardAlt }}"
            @class([
                'aspect-[4/5] sm:aspect-[4/3] w-full transition duration-500 ease-out group-hover:scale-105',
                'object-cover' => $hasProductImage,
                'object-contain bg-[rgba(253,240,213,0.50)] p-12 sm:p-14' => ! $hasProductImage,
            ])
            loading="lazy"
            decoding="async"
        >
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
