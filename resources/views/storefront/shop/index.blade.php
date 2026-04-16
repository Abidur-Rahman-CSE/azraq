<x-layouts.storefront
    :title="$title.' | '.config('brand.name')"
    :description="$description"
    :schema-data="[
        [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $title,
            'url' => url()->current(),
            'description' => $description,
        ],
    ]"
>
    <section class="section-shell">
        <div class="container-shell">
            <div class="space-y-8">
                <div class="surface-card-featured grid gap-8 p-8 lg:grid-cols-[1.05fr_0.95fr] lg:p-10">
                    <div>
                        <span class="eyebrow">
                            {{ $currentCategory ? 'Category view' : ($currentCollection ? 'Collection view' : 'Shop view') }}
                        </span>
                        <h1 class="mt-5 text-5xl font-semibold tracking-[-0.03em] text-[var(--text-main)]">{{ $title }}</h1>
                        <p class="mt-5 max-w-3xl text-base leading-8 text-[var(--text-muted)]">{{ $description }}</p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <x-storefront.trust-badge :label="$products->total().' products found'" />
                            @if ($filters['applied']['type'])
                                <x-storefront.trust-badge :label="'Type: '.collect($filters['productTypes'])->firstWhere('value', $filters['applied']['type'])['label']" />
                            @endif
                            @if ($filters['applied']['tag'])
                                <x-storefront.trust-badge :label="'Tag: '.str($filters['applied']['tag'])->headline()" />
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        @foreach (($heroCollections ?? collect())->take(3) as $collection)
                            <x-storefront.collection-card :collection="$collection" />
                        @endforeach
                    </div>
                </div>

                @if (($featuredStrip ?? collect())->isNotEmpty())
                    <div class="grid gap-6 lg:grid-cols-3">
                        @foreach ($featuredStrip as $product)
                            <x-storefront.listing-card :product="$product" />
                        @endforeach
                    </div>
                @endif

                <div class="grid gap-8 lg:grid-cols-[320px_1fr]">
                <aside class="space-y-6">
                    <x-storefront.filter-panel :filters="$filters" :action="url()->current()" />

                    <div class="surface-card p-6">
                        <p class="text-sm font-semibold text-[var(--text-main)]">Browse taxonomy</p>
                        <div class="mt-4 space-y-3 text-sm">
                            @foreach ($filters['categories'] as $category)
                                <a href="{{ route('categories.show', $category) }}" class="block text-[var(--text-muted)] transition hover:text-[var(--text-main)]">
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </aside>

                <div class="space-y-8">
                    <div class="flex flex-wrap items-center justify-between gap-4 rounded-[var(--radius-2xl)] border border-[var(--border-soft)] bg-white/75 px-6 py-5">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-[var(--accent-primary)]">Catalog view</p>
                            <p class="mt-2 text-sm text-[var(--text-muted)]">Filter, sort, and browse the Azraq catalog with tailored category and collection context.</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <span class="button-pill">{{ $products->total() }} items</span>
                            <span class="button-pill">{{ $currentCollection ? 'Editorial collection' : ($currentCategory ? 'Curated category' : 'All products') }}</span>
                        </div>
                    </div>

                    @if ($currentCollection)
                        <div class="grid gap-4 md:grid-cols-3">
                            @foreach ($filters['collections']->take(3) as $collection)
                                <x-storefront.collection-card :collection="$collection" />
                            @endforeach
                        </div>
                    @endif

                    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @forelse ($products as $product)
                            <x-storefront.listing-card :product="$product" />
                        @empty
                            <div class="surface-card p-8 md:col-span-2 xl:col-span-3">
                                <h2 class="text-2xl font-semibold text-[var(--text-main)]">No products match this filter set.</h2>
                                <p class="mt-4 text-sm leading-7 text-[var(--text-muted)]">Try removing a tag or product type filter, or browse the full shop listing.</p>
                            </div>
                        @endforelse
                    </div>

                    {{ $products->links() }}
                </div>
            </div>
            </div>
        </div>
    </section>
</x-layouts.storefront>
