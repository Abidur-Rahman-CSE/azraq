@props(['collection'])

<a href="{{ route('collections.show', $collection) }}" class="group overflow-hidden rounded-[var(--radius-3xl)] border border-[var(--border-soft)] bg-white transition hover:-translate-y-1 hover:shadow-[var(--shadow-card-hover)]">
    <div class="relative aspect-[16/10] overflow-hidden bg-[var(--bg-section-soft)]">
        @if ($collection->cover_image_url)
            <img
                src="{{ $collection->cover_image_url }}"
                alt="{{ $collection->name }}"
                class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                loading="lazy"
                decoding="async"
            >
        @else
            <div class="h-full w-full bg-[radial-gradient(circle_at_top_right,_rgba(187,145,92,0.24),_transparent_40%),linear-gradient(180deg,rgba(250,246,240,1),rgba(241,233,221,1))]"></div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-[rgba(26,28,42,0.62)] via-transparent to-transparent"></div>
        <span class="absolute left-5 top-5 rounded-full bg-white/85 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--accent-primary)] backdrop-blur-sm">Collection</span>
    </div>

    <div class="p-6">
        <h3 class="font-serif text-2xl font-semibold text-[var(--text-main)]">{{ $collection->name }}</h3>
        <p class="mt-3 text-sm leading-7 text-[var(--text-muted)]">
            {{ \Illuminate\Support\Str::limit(strip_tags($collection->description), 110) }}
        </p>
        <div class="mt-6 flex items-center justify-between gap-4">
            <p class="text-sm font-medium text-[var(--accent-secondary)]">{{ $collection->products_count }} curated products</p>
            <span class="text-sm font-semibold text-[var(--accent-primary)]">View collection</span>
        </div>
    </div>
</a>
