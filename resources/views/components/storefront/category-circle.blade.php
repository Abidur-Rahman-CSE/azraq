@props(['category'])

@php
    $img = $category->image_url ?: $category->banner_image_url;
@endphp

<a href="{{ route('categories.show', $category) }}" class="category-circle group" aria-label="Browse {{ $category->name }}">
    <span class="category-circle__disc">
        @if ($img)
            <img src="{{ $img }}" alt="{{ $category->alt_text ?: $category->name }}" loading="lazy" decoding="async">
        @else
            <span class="category-circle__fallback"></span>
        @endif
    </span>
    <span class="category-circle__label">{{ $category->name }}</span>
    @if (!is_null($category->products_count))
        <span class="category-circle__count">{{ $category->products_count }} pcs</span>
    @endif
</a>
