<x-layouts.storefront title="Search | Azraq Bridal">
    <div class="section-shell">
        <div class="container-shell">
            <div class="grid gap-8 lg:grid-cols-[300px_1fr]">
                <x-storefront.filter-panel :filters="$filters" :action="route('search.index')" />

                <div class="space-y-6">
                    <section class="surface-card-featured p-8">
                        <span class="eyebrow">Search</span>
                        <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">
                            @if ($queryText !== '')
                                Results for “{{ $queryText }}”
                            @else
                                Search the Azraq catalog
                            @endif
                        </h1>
                        <p class="mt-4 max-w-3xl text-base leading-8 text-[var(--color-text-soft)]">
                            Browse mixed result types across standard bridal details, personalized Nikah products, curated combos, and service bookings.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            @foreach ($suggestionChips as $suggestion)
                                <a href="{{ route('search.index', ['search' => $suggestion]) }}" class="button-pill">{{ str($suggestion)->headline() }}</a>
                            @endforeach
                        </div>
                    </section>

                    @if ($products->isEmpty())
                        <section class="surface-card p-10 text-center">
                            <h2 class="text-3xl font-semibold text-[var(--color-secondary-900)]">No matching results yet</h2>
                            <p class="mx-auto mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-soft)]">
                                Try a broader search term, explore a curated collection, or browse the main shop to discover bridal accessories, personalized Nikah pieces, bundles, and booking experiences.
                            </p>
                            <div class="mt-8 flex flex-wrap justify-center gap-3">
                                <a href="{{ route('shop.index') }}" class="button-primary">Browse all products</a>
                                <a href="{{ route('collections.show', 'signature-nikah') }}" class="button-ghost">Open Signature Nikah</a>
                            </div>
                        </section>
                    @else
                        <section class="surface-card p-8">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Search results</p>
                                    <h2 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ $products->total() }} result{{ $products->total() === 1 ? '' : 's' }}</h2>
                                </div>
                                <p class="text-sm text-[var(--color-text-soft)]">Filtered by type, tag, and sort controls on the left.</p>
                            </div>

                            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                @foreach ($products as $product)
                                    <x-storefront.listing-card :product="$product" />
                                @endforeach
                            </div>

                            <div class="mt-8">
                                {{ $products->links() }}
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.storefront>
