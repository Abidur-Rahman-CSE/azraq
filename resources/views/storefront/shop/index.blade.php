@php
    $appliedFilters = $filters['applied'];
    $availabilityLabels = [
        'in_stock' => 'In stock only',
        'made_to_order' => 'Made to order',
    ];
    $activeFilterChips = collect();
    $selectedCategory = $filters['selectedCategory'] ?? null;
    $bannerCategory = $currentCollection ? null : $selectedCategory;
    $selectedCategoryBanner = $currentCollection?->cover_image_url ?: ($bannerCategory?->banner_image_url ?: $bannerCategory?->image_url);
    $selectedCategoryMobileBanner = $currentCollection ? null : $bannerCategory?->mobile_banner_image_url;
    $bannerTitle = $currentCollection?->name ?: $selectedCategory?->name;
    $bannerDescription = $currentCollection?->description ?: ($selectedCategory?->storefront_excerpt ?: $selectedCategory?->description);
    $bannerEyebrow = $currentCollection ? 'Collection' : 'Category';

    if (! $currentCollection && ! $selectedCategoryBanner && $selectedCategory?->parent) {
        $bannerCategory = $selectedCategory->parent;
        $selectedCategoryBanner = $bannerCategory->banner_image_url ?: $bannerCategory->image_url;
        $selectedCategoryMobileBanner = $bannerCategory->mobile_banner_image_url;
    }

    $categoryBreadcrumb = collect();
    $breadcrumbNode = $selectedCategory;

    while ($breadcrumbNode) {
        $categoryBreadcrumb->prepend($breadcrumbNode);
        $breadcrumbNode = $breadcrumbNode->parent;
    }

    $clearCategoryQuery = request()->except(['category', 'page']);
    $clearCategoryUrl = $currentCollection
        ? url()->current().($clearCategoryQuery ? '?'.http_build_query($clearCategoryQuery) : '')
        : route('shop.index').($clearCategoryQuery ? '?'.http_build_query($clearCategoryQuery) : '');
    $clearAllUrl = $currentCollection ? route('collections.show', $currentCollection) : route('shop.index');

    if (filled($appliedFilters['category'] ?? null)) {
        $activeFilterChips->push([
            'label' => 'Category: '.($selectedCategory?->name ?? str($appliedFilters['category'])->headline()),
            'url' => $clearCategoryUrl,
        ]);
    }

    if (filled($appliedFilters['type'] ?? null)) {
        $type = collect($filters['productTypes'])->firstWhere('value', $appliedFilters['type']);
        $activeFilterChips->push([
            'label' => 'Type: '.($type['label'] ?? str($appliedFilters['type'])->headline()),
            'url' => url()->current().'?'.http_build_query(request()->except(['type', 'page'])),
        ]);
    }

    if (filled($appliedFilters['tag'] ?? null)) {
        $tag = collect($filters['tags'])->firstWhere('slug', $appliedFilters['tag']);
        $activeFilterChips->push([
            'label' => 'Tag: '.($tag?->name ?? str($appliedFilters['tag'])->headline()),
            'url' => url()->current().'?'.http_build_query(request()->except(['tag', 'page'])),
        ]);
    }

    if (filled($appliedFilters['min_price'] ?? null) || filled($appliedFilters['max_price'] ?? null)) {
        $min = filled($appliedFilters['min_price'] ?? null) ? 'BDT '.number_format((float) $appliedFilters['min_price'], 0) : 'Any';
        $max = filled($appliedFilters['max_price'] ?? null) ? 'BDT '.number_format((float) $appliedFilters['max_price'], 0) : 'Any';
        $activeFilterChips->push([
            'label' => 'Price: '.$min.' - '.$max,
            'url' => url()->current().'?'.http_build_query(request()->except(['min_price', 'max_price', 'page'])),
        ]);
    }

    foreach (collect($appliedFilters['availability'] ?? []) as $availability) {
        $remainingAvailability = collect($appliedFilters['availability'] ?? [])
            ->reject(fn ($value) => $value === $availability)
            ->values()
            ->all();
        $query = request()->except(['availability', 'page']);
        if ($remainingAvailability) {
            $query['availability'] = $remainingAvailability;
        }

        $activeFilterChips->push([
            'label' => 'Availability: '.($availabilityLabels[$availability] ?? str($availability)->headline()),
            'url' => url()->current().'?'.http_build_query($query),
        ]);
    }

    if (filled($appliedFilters['search'] ?? null)) {
        $activeFilterChips->push([
            'label' => 'Search: '.$appliedFilters['search'],
            'url' => url()->current().'?'.http_build_query(request()->except(['search', 'page'])),
        ]);
    }

    if (filled($appliedFilters['sort'] ?? null)) {
        $sortLabels = [
            'price_low' => 'Price low to high',
            'price_high' => 'Price high to low',
            'name' => 'Name',
        ];
        $activeFilterChips->push([
            'label' => 'Sort: '.($sortLabels[$appliedFilters['sort']] ?? str($appliedFilters['sort'])->headline()),
            'url' => url()->current().'?'.http_build_query(request()->except(['sort', 'page'])),
        ]);
    }
@endphp

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
            <div class="space-y-8" x-data="{ filtersOpen: false }">
                <div class="grid gap-6 lg:grid-cols-[300px_1fr] lg:gap-8">
                    <aside class="hidden space-y-5 lg:block">
                        <x-storefront.filter-panel :filters="$filters" :action="url()->current()" />
                    </aside>

                    <div class="min-w-0 space-y-6">
                        @if ($selectedCategoryBanner && ($currentCollection || $selectedCategory))
                            <div class="relative overflow-hidden rounded-[var(--radius-2xl)] border border-[var(--border-soft)] bg-[var(--bg-section-soft)] shadow-sm">
                                <picture>
                                    @if ($selectedCategoryMobileBanner)
                                        <source media="(max-width: 640px)" srcset="{{ $selectedCategoryMobileBanner }}">
                                    @endif
                                    <img
                                        src="{{ $selectedCategoryBanner }}"
                                        alt="{{ $currentCollection?->name ?: ($bannerCategory?->alt_text ?: $selectedCategory->name) }}"
                                        class="h-40 w-full object-cover sm:h-52 lg:h-64"
                                        loading="eager"
                                        decoding="async"
                                    >
                                </picture>
                                <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/35 to-transparent"></div>
                                <div
                                    class="absolute inset-y-0 left-0 w-full bg-white/14 backdrop-blur-[3px] sm:w-[72%] lg:w-[64%]"
                                    style="-webkit-mask-image: linear-gradient(90deg, #000 0%, #000 54%, rgba(0,0,0,0.55) 74%, transparent 100%); mask-image: linear-gradient(90deg, #000 0%, #000 54%, rgba(0,0,0,0.55) 74%, transparent 100%);"
                                    aria-hidden="true"
                                ></div>
                                <div class="absolute inset-y-0 left-0 flex max-w-2xl flex-col justify-end p-5 text-white drop-shadow-[0_2px_16px_rgba(0,0,0,0.45)] sm:p-6 lg:p-8">
                                    <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.16em] text-white/82" aria-label="Category breadcrumb">
                                        <a href="{{ route('shop.index') }}" class="transition hover:text-white">Shop</a>
                                        <span class="text-white/55">&gt;</span>
                                        @if ($currentCollection)
                                            <span class="text-white">{{ $currentCollection->name }}</span>
                                        @else
                                            @foreach ($categoryBreadcrumb as $crumb)
                                                @if (! $loop->last)
                                                    <a href="{{ route('categories.show', $crumb) }}" class="transition hover:text-white">{{ $crumb->name }}</a>
                                                    <span class="text-white/55">&gt;</span>
                                                @else
                                                    <span class="text-white">{{ $crumb->name }}</span>
                                                @endif
                                            @endforeach
                                        @endif
                                    </nav>
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/85">{{ $bannerEyebrow }}</p>
                                    <h1 class="mt-1 font-serif text-3xl font-semibold leading-tight text-white sm:text-4xl">{{ $bannerTitle }}</h1>
                                    @if (filled($bannerDescription))
                                        <p class="mt-2 max-w-xl text-sm leading-6 text-white/90">{{ \Illuminate\Support\Str::limit(strip_tags($bannerDescription), 140) }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-wrap items-start justify-between gap-3 rounded-[var(--radius-xl)] border border-[var(--border-soft)] bg-white/80 px-4 py-3 shadow-sm sm:px-5">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--accent-primary)]">All products</p>
                                <p class="mt-1 truncate text-xs text-[var(--text-muted)]">{{ $currentCollection ? 'Editorial collection' : ($currentCategory ? 'Curated category' : 'Full catalog') }}</p>
                                @if ($activeFilterChips->isNotEmpty())
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($activeFilterChips as $chip)
                                            <a href="{{ $chip['url'] }}" class="inline-flex max-w-full items-center gap-1.5 rounded-full border border-[rgba(120,0,0,0.18)] bg-[rgba(120,0,0,0.06)] px-3 py-1 text-[11px] font-semibold text-[var(--accent-primary)] transition hover:border-[var(--accent-primary)]">
                                                <span class="min-w-0 truncate">{{ $chip['label'] }}</span>
                                                <span aria-hidden="true">×</span>
                                            </a>
                                        @endforeach
                                        <a href="{{ $clearAllUrl }}" class="inline-flex items-center rounded-full border border-[var(--border-soft)] bg-white/80 px-3 py-1 text-[11px] font-semibold text-[var(--text-muted)] transition hover:text-[var(--accent-primary)]">
                                            Clear all
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <x-storefront.trust-badge :label="$products->total().' products found'" />
                                <button
                                    type="button"
                                    class="button-ghost !rounded-full !px-3 !py-2 !text-xs lg:hidden"
                                    @click="filtersOpen = !filtersOpen"
                                    :aria-expanded="filtersOpen.toString()"
                                    aria-controls="shop-mobile-filters"
                                >
                                    Filters
                                </button>
                            </div>
                        </div>

                        <div
                            id="shop-mobile-filters"
                            class="lg:hidden"
                            x-cloak
                            x-show="filtersOpen"
                            x-transition.duration.200ms
                        >
                            <x-storefront.filter-panel :filters="$filters" :action="url()->current()" />
                        </div>

                        <div class="grid grid-cols-2 gap-3 sm:gap-6 xl:grid-cols-3">
                            @forelse ($products as $product)
                                <x-storefront.listing-card :product="$product" />
                            @empty
                                <div class="surface-card col-span-2 p-6 xl:col-span-3">
                                    <h2 class="text-xl font-semibold text-[var(--text-main)] sm:text-2xl">No products match this filter set.</h2>
                                    <p class="mt-3 text-sm leading-7 text-[var(--text-muted)]">Try removing a tag or product type filter, or browse the full shop listing.</p>
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
