@props(['category'])

@php
    $categoryImage = $category->banner_image_url ?: $category->image_url;
@endphp

<a href="{{ route('categories.show', $category) }}" class="group relative block overflow-hidden rounded-[var(--radius-3xl)] transition hover:-translate-y-1 hover:shadow-[var(--shadow-card-hover)]">
    <div class="relative aspect-[4/5] bg-[var(--bg-section-soft)]">
        @if ($categoryImage)
            <img
                src="{{ $categoryImage }}"
                alt="{{ $category->alt_text ?: $category->name }}"
                class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                loading="lazy"
                decoding="async"
            >
        @else
            <div class="h-full w-full bg-[radial-gradient(circle_at_top,_rgba(187,145,92,0.18),_transparent_52%),linear-gradient(180deg,rgba(255,255,255,0.92),rgba(244,237,228,0.86))]"></div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-[rgba(26,28,42,0.72)] via-[rgba(26,28,42,0.2)] to-transparent"></div>

        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-7">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/75">Category</p>
            <h3 class="mt-3 font-serif text-3xl font-semibold leading-tight text-white">{{ $category->name }}</h3>
            @if (filled($category->storefront_excerpt ?: $category->description))
                <p class="mt-3 max-w-sm text-sm leading-6 text-white/80">
                    {{ \Illuminate\Support\Str::limit($category->storefront_excerpt ?: strip_tags($category->description), 88) }}
                </p>
            @endif
            <div class="mt-5 flex items-center justify-between gap-4">
                <p class="text-sm font-medium text-white/80">{{ $category->products_count }} products</p>
                <span class="rounded-full border border-white/30 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-white backdrop-blur-sm">Explore</span>
            </div>
        </div>
    </div>
</a>
