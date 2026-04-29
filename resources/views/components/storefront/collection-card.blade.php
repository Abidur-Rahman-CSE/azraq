@props(['collection'])

<a href="{{ route('collections.show', $collection) }}" class="collection-card-lux group block">
    <div class="relative aspect-[16/10] overflow-hidden rounded-t-[var(--radius-3xl)] bg-[var(--bg-section-soft)]">
        @if ($collection->cover_image_url)
            <img
                src="{{ $collection->cover_image_url }}"
                alt="{{ $collection->name }}"
                class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                loading="lazy"
                decoding="async"
            >
        @else
            <div class="h-full w-full bg-[radial-gradient(circle_at_top_right,_rgba(193,18,31,0.10),_transparent_40%),linear-gradient(180deg,rgba(253,240,213,0.90),rgba(241,233,221,1))]"></div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-[rgba(7,14,24,0.55)] via-transparent to-transparent"></div>
        <span class="section-kicker absolute left-5 top-5 rounded-full bg-white/85 px-3 py-1 text-[0.62rem] backdrop-blur-sm">Collection</span>
    </div>

    <div class="p-6">
        <h3 class="text-xl font-semibold leading-snug text-[var(--text-main)]">{{ $collection->name }}</h3>
        <p class="mt-3 text-sm leading-7 text-[var(--text-muted)]">
            {{ \Illuminate\Support\Str::limit(strip_tags($collection->description), 110) }}
        </p>
        <div class="mt-5 flex items-center justify-between gap-4">
            <p class="text-[0.72rem] font-medium uppercase tracking-[0.14em] text-[var(--accent-secondary)]">{{ $collection->products_count }} curated products</p>
            <span class="text-sm font-semibold text-[var(--accent-primary)]">View →</span>
        </div>
    </div>
</a>
