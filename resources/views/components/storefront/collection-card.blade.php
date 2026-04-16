@props(['collection'])

<a href="{{ route('collections.show', $collection) }}" class="surface-card block p-6 transition hover:-translate-y-1 hover:shadow-[var(--shadow-card-hover)]">
    <span class="eyebrow">Collection</span>
    <h3 class="mt-5 text-2xl font-semibold text-[var(--text-main)]">{{ $collection->name }}</h3>
    <p class="mt-4 text-sm leading-7 text-[var(--text-muted)]">{{ $collection->description }}</p>
    <div class="mt-6 flex items-center justify-between gap-4">
        <p class="text-sm font-medium text-[var(--accent-secondary)]">{{ $collection->products_count }} curated products</p>
        <span class="text-sm font-semibold text-[var(--accent-primary)]">View edit</span>
    </div>
</a>
