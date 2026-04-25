@props(['product'])

<article class="surface-product flex h-full flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-[var(--shadow-medium)]">
    @php($primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first())
    @php($cardImage = $product->storefront_preview_image_url)
    @php($cardAlt = $primaryImage?->label ?: $product->name)
    @if ($cardImage)
        <img
            src="{{ $cardImage }}"
            alt="{{ $cardAlt }}"
            class="aspect-[4/3] w-full object-cover"
            loading="lazy"
            decoding="async"
        >
    @else
        <div class="flex aspect-[4/3] items-center justify-center bg-[var(--color-surface-cream)] text-sm text-[var(--color-text-soft)]">
            Product preview coming soon
        </div>
    @endif

    <div class="relative p-6">
        <div class="flex items-start justify-between gap-4">
            <span class="eyebrow">{{ $product->type?->label() }}</span>
            <span class="rounded-full bg-[rgba(0,48,73,0.08)] px-3 py-2 text-xs font-medium text-[var(--accent-secondary)]">
                {{ $product->manage_stock ? ($product->stock_quantity > 0 ? 'In stock' : 'Out of stock') : 'Made to order' }}
            </span>
        </div>

        <div class="mt-7">
            <p class="text-sm font-medium uppercase tracking-[0.12em] text-[var(--accent-primary)]">{{ $product->category?->name }}</p>
            <h3 class="mt-3 text-[1.65rem] font-semibold leading-tight text-[var(--text-main)]">{{ $product->name }}</h3>
            <p class="mt-4 text-sm leading-7 text-[var(--text-muted)]">{{ $product->excerpt ?: $product->description }}</p>
        </div>

        @if ($product->tags->isNotEmpty())
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($product->tags->take(3) as $tag)
                    <span class="rounded-full bg-[rgba(102,155,188,0.12)] px-3 py-2 text-xs font-medium text-[var(--accent-secondary)]">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-auto flex items-center justify-between border-t border-[var(--border-soft)] px-6 py-5">
        <div>
            <p class="text-lg font-semibold text-[var(--text-main)]">BDT {{ number_format((float) $product->price, 0) }}</p>
            @if ($product->compare_at_price)
                <p class="text-sm text-[var(--text-muted)] line-through">BDT {{ number_format((float) $product->compare_at_price, 0) }}</p>
            @endif
        </div>

        <a href="{{ route('products.show', $product) }}" class="button-ghost" aria-label="View details for {{ $product->name }}">View details</a>
    </div>
</article>
