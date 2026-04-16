<x-layouts.admin title="Admin Dashboard | Azraq Bridal">
    <div class="space-y-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Phase 1 overview</p>
                <h2 class="mt-3 text-4xl font-semibold text-[var(--color-secondary-900)]">Catalog admin foundation</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-[var(--color-text-soft)]">
                    Categories, collections, tags, products, bundle items, service metadata, and variant data are now modeled separately so future storefront, checkout, and personalization flows can build on a clean catalog structure.
                </p>
            </div>
            <a href="{{ route('admin.catalog.products.create') }}" class="button-primary">Add product</a>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <article class="surface-card p-6">
                    <p class="text-sm text-[var(--color-text-soft)]">{{ $stat['label'] }}</p>
                    <p class="mt-4 text-4xl font-semibold text-[var(--color-secondary-900)]">{{ $stat['value'] }}</p>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">{{ $stat['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
