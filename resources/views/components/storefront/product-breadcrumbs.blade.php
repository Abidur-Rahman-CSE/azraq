@props(['product'])

<nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-sm text-[var(--color-text-soft)]">
    <a href="{{ route('home') }}" class="transition hover:text-[var(--color-primary-900)]">Home</a>
    <span>/</span>
    @if ($product->category)
        <a href="{{ route('categories.show', $product->category) }}" class="transition hover:text-[var(--color-primary-900)]">{{ $product->category->name }}</a>
        <span>/</span>
    @endif
    <span class="text-[var(--color-secondary-900)]">{{ $product->name }}</span>
</nav>
