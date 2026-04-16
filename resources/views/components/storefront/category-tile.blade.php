@props(['category'])

<a href="{{ route('categories.show', $category) }}" class="surface-card-featured block p-7 transition hover:-translate-y-1 hover:shadow-[var(--shadow-card-hover)]">
    <p class="text-sm font-medium uppercase tracking-[0.18em] text-[var(--accent-primary)]">Category</p>
    <h3 class="mt-5 text-3xl font-semibold text-[var(--text-main)]">{{ $category->name }}</h3>
    <p class="mt-4 text-sm leading-7 text-[var(--text-muted)]">{{ $category->description }}</p>
    <div class="mt-8 flex items-center justify-between gap-4">
        <p class="text-sm font-medium text-[var(--accent-secondary)]">{{ $category->products_count }} products</p>
        <span class="button-pill">Explore</span>
    </div>
</a>
