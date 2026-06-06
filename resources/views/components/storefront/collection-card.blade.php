@props(['collection'])

@php
    $thumbs = $collection->relationLoaded('products')
        ? $collection->products
        : $collection->products()->with('images')->take(3)->get();
    $thumbs = $thumbs->take(3);
    $count = $collection->products_count ?? $collection->products()->count();
@endphp

<a href="{{ route('collections.show', $collection) }}" class="collection-card-rich group">
    <div class="collection-card-rich__cover">
        @if ($collection->cover_image_url)
            <img src="{{ $collection->cover_image_url }}" alt="{{ $collection->name }}" loading="lazy" decoding="async">
        @else
            <div class="collection-card-rich__cover-fallback"></div>
        @endif
        <span class="collection-card-rich__cover-kicker">Collection</span>
    </div>

    <div class="collection-card-rich__body">
        <h3 class="collection-card-rich__title">{{ $collection->name }}</h3>
        @if (filled($collection->description))
            <p class="collection-card-rich__excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($collection->description), 110) }}</p>
        @endif

        @if ($thumbs->isNotEmpty())
            <div class="collection-card-rich__thumbs">
                @foreach ($thumbs as $thumb)
                    @php($thumbImage = $thumb->storefront_preview_image_url ?? null)
                    <span class="collection-card-rich__thumb">
                        @if ($thumbImage)
                            <img src="{{ $thumbImage }}" alt="{{ $thumb->name }}" loading="lazy" decoding="async">
                        @endif
                    </span>
                @endforeach
            </div>
        @endif

        <div class="collection-card-rich__footer">
            <span class="collection-card-rich__count">{{ $count }} items</span>
            <span class="collection-card-rich__cta">View all →</span>
        </div>
    </div>
</a>
