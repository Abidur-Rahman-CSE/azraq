@props(['category', 'variant' => 'default'])

@php
    $categoryImage = $category->banner_image_url ?: $category->image_url;
    $isHero = $variant === 'hero';
@endphp

<a href="{{ route('categories.show', $category) }}" @class([
    'category-card-lux group relative block overflow-hidden',
    'category-card-lux--hero' => $isHero,
])>
    @if ($categoryImage)
        <img
            src="{{ $categoryImage }}"
            alt="{{ $category->alt_text ?: $category->name }}"
            class="category-card-lux-bg"
            loading="lazy"
            decoding="async"
        >
    @else
        <div class="category-card-lux-fallback"></div>
    @endif

    <div class="category-card-lux-overlay"></div>

    <div class="category-card-lux-content">
        <p class="section-kicker text-[0.58rem] text-white/70">Category</p>
        <h3 class="category-card-lux__title">{{ $category->name }}</h3>
        @if (filled($category->storefront_excerpt ?: $category->description))
            <p class="category-card-lux__excerpt">
                {{ \Illuminate\Support\Str::limit($category->storefront_excerpt ?: strip_tags($category->description), 80) }}
            </p>
        @endif
        <div class="category-card-lux__footer">
            <p class="text-[0.65rem] font-medium uppercase tracking-[0.16em] text-white/70">{{ $category->products_count }} pcs</p>
            <span class="category-card-lux__cta">Explore →</span>
        </div>
    </div>
</a>
