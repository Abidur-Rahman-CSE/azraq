@props([
    'filters',
    'action',
])

@php
    $applied = $filters['applied'];
    $availability = collect($applied['availability'] ?? []);
    $priceMin = $applied['min_price'] ?? null;
    $priceMax = $applied['max_price'] ?? null;
    $boundsMin = (int) data_get($filters, 'priceBounds.min', 0);
    $boundsMax = max((int) data_get($filters, 'priceBounds.max', 0), $boundsMin);
    $selectedCategory = $filters['selectedCategory'] ?? null;
    $clearUrl = $selectedCategory ? route('shop.index') : $action;
    $parentCategories = collect($filters['parentCategories'] ?? $filters['categories']);
    $selectedAncestorIds = collect();
    $ancestor = $selectedCategory;

    while ($ancestor) {
        $selectedAncestorIds->push($ancestor->id);
        $ancestor = $ancestor->parent;
    }
@endphp

<form method="GET" action="{{ $action }}" class="surface-sidebar max-w-full border-[rgba(120,0,0,0.12)] bg-[rgba(253,240,213,0.48)] p-5 sm:p-6">
    @if ($applied['category'])
        <input type="hidden" name="category" value="{{ $applied['category'] }}">
    @endif
    @if ($applied['type'])
        <input type="hidden" name="type" value="{{ $applied['type'] }}">
    @endif
    @if ($applied['tag'])
        <input type="hidden" name="tag" value="{{ $applied['tag'] }}">
    @endif

    <div class="space-y-8">
        <div>
            <div class="flex items-center justify-between border-b border-[rgba(120,0,0,0.18)] pb-3">
                <h2 class="text-sm font-semibold uppercase tracking-[0.24em] text-[var(--accent-primary)]">Categories</h2>
                @if ($applied['category'] || $applied['search'] || $applied['type'] || $applied['tag'] || $applied['sort'] || $priceMin || $priceMax || $availability->isNotEmpty())
                    <a href="{{ $clearUrl }}" class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--text-muted)] transition hover:text-[var(--accent-primary)]">Clear</a>
                @endif
            </div>

            <div class="mt-4 space-y-1">
                @foreach ($parentCategories as $category)
                    @php
                        $children = collect($category->children ?? []);
                        $isSelected = $selectedCategory?->is($category);
                        $isExpanded = $selectedAncestorIds->contains($category->id);
                        $categoryQuery = request()->except(['category', 'page']);
                        $categoryUrl = route('categories.show', $category).($categoryQuery ? '?'.http_build_query($categoryQuery) : '');
                    @endphp
                    <a
                        href="{{ $categoryUrl }}"
                        class="flex items-center justify-between gap-4 rounded-lg px-1 py-2 text-sm transition hover:text-[var(--accent-primary)]"
                    >
                        <span class="{{ $isExpanded ? 'font-semibold text-[var(--accent-primary)]' : 'font-medium text-[var(--text-muted)]' }}">{{ $category->name }}</span>
                        <span class="flex items-center gap-2 text-xs font-semibold text-[rgba(95,73,64,0.58)]">
                            <span>{{ str_pad((string) $category->products_count, 2, '0', STR_PAD_LEFT) }}</span>
                            @if ($children->isNotEmpty())
                                <span class="text-sm text-[var(--accent-primary)]">{{ $isExpanded ? 'v' : '>' }}</span>
                            @endif
                        </span>
                    </a>

                    @if ($isExpanded && $children->isNotEmpty())
                        <div class="ml-3 border-l border-[rgba(120,0,0,0.14)] pl-3">
                            @foreach ($children as $child)
                                @php
                                    $grandchildren = collect($child->children ?? []);
                                    $isChildSelected = $selectedCategory?->is($child);
                                    $isChildExpanded = $selectedAncestorIds->contains($child->id);
                                    $childQuery = request()->except(['category', 'page']);
                                    $childUrl = route('categories.show', $child).($childQuery ? '?'.http_build_query($childQuery) : '');
                                @endphp
                                <a href="{{ $childUrl }}" class="flex items-center justify-between gap-3 rounded-md px-1 py-1.5 text-sm transition hover:text-[var(--accent-primary)]">
                                    <span class="{{ $isChildExpanded ? 'font-semibold text-[var(--accent-primary)]' : 'text-[var(--text-muted)]' }}">{{ $child->name }}</span>
                                    <span class="flex items-center gap-2 text-xs font-semibold text-[rgba(95,73,64,0.58)]">
                                        <span>{{ str_pad((string) $child->products_count, 2, '0', STR_PAD_LEFT) }}</span>
                                        @if ($grandchildren->isNotEmpty())
                                            <span class="text-sm text-[var(--accent-primary)]">{{ $isChildExpanded ? 'v' : '>' }}</span>
                                        @endif
                                    </span>
                                </a>

                                @if ($isChildExpanded && $grandchildren->isNotEmpty())
                                    <div class="ml-4 border-l border-[rgba(120,0,0,0.10)] pl-3">
                                        @foreach ($grandchildren as $grandchild)
                                            @php
                                                $grandchildQuery = request()->except(['category', 'page']);
                                                $grandchildUrl = route('categories.show', $grandchild).($grandchildQuery ? '?'.http_build_query($grandchildQuery) : '');
                                                $isGrandchildSelected = $selectedCategory?->is($grandchild);
                                            @endphp
                                            <a href="{{ $grandchildUrl }}" class="flex items-center justify-between gap-3 rounded-md px-1 py-1.5 text-sm transition hover:text-[var(--accent-primary)]">
                                                <span class="{{ $isGrandchildSelected ? 'font-semibold text-[var(--accent-primary)]' : 'text-[var(--text-muted)]' }}">{{ $grandchild->name }}</span>
                                                <span class="text-xs font-semibold text-[rgba(95,73,64,0.58)]">{{ str_pad((string) $grandchild->products_count, 2, '0', STR_PAD_LEFT) }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div>
            <h2 class="border-b border-[rgba(120,0,0,0.18)] pb-3 text-sm font-semibold uppercase tracking-[0.24em] text-[var(--accent-primary)]">Price range</h2>
            <div class="mt-5">
                <input
                    type="range"
                    min="{{ $boundsMin }}"
                    max="{{ $boundsMax }}"
                    value="{{ $priceMax ?: $boundsMax }}"
                    oninput="this.form.elements.max_price.value = this.value;"
                    class="w-full accent-[var(--accent-primary)]"
                    aria-label="Maximum price"
                >
                <div class="mt-4 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                    <input
                        type="number"
                        min="0"
                        name="min_price"
                        value="{{ $priceMin }}"
                        placeholder="BDT {{ number_format($boundsMin, 0) }}"
                        class="field-input !rounded-none !border-[rgba(120,0,0,0.10)] !bg-white/70 !px-3 !py-2 !text-center !text-xs !font-semibold !text-[var(--accent-secondary)]"
                    >
                    <span class="text-xs font-semibold text-[var(--text-muted)]">to</span>
                    <input
                        type="number"
                        min="0"
                        name="max_price"
                        value="{{ $priceMax }}"
                        placeholder="BDT {{ number_format($boundsMax, 0) }}+"
                        class="field-input !rounded-none !border-[rgba(120,0,0,0.10)] !bg-white/70 !px-3 !py-2 !text-center !text-xs !font-semibold !text-[var(--accent-secondary)]"
                    >
                </div>
            </div>
        </div>

        <div>
            <h2 class="border-b border-[rgba(120,0,0,0.18)] pb-3 text-sm font-semibold uppercase tracking-[0.24em] text-[var(--accent-primary)]">Availability</h2>
            <div class="mt-4 space-y-3">
                <label class="flex cursor-pointer items-center gap-3 text-sm font-medium text-[var(--text-muted)]">
                    <input
                        type="checkbox"
                        name="availability[]"
                        value="in_stock"
                        @checked($availability->contains('in_stock'))
                        class="h-5 w-5 rounded-sm border-[rgba(120,0,0,0.24)] text-[var(--accent-primary)] accent-[var(--accent-primary)]"
                    >
                    <span>In Stock Only</span>
                </label>
                <label class="flex cursor-pointer items-center gap-3 text-sm font-medium text-[var(--text-muted)]">
                    <input
                        type="checkbox"
                        name="availability[]"
                        value="made_to_order"
                        @checked($availability->contains('made_to_order'))
                        class="h-5 w-5 rounded-sm border-[rgba(120,0,0,0.24)] text-[var(--accent-primary)] accent-[var(--accent-primary)]"
                    >
                    <span>Made to Order</span>
                </label>
            </div>
        </div>

        <div>
            <h2 class="border-b border-[rgba(120,0,0,0.18)] pb-3 text-sm font-semibold uppercase tracking-[0.24em] text-[var(--accent-primary)]">Search & sort</h2>
            <div class="mt-4 space-y-3">
                <input
                    type="text"
                    name="search"
                    value="{{ $applied['search'] }}"
                    placeholder="Search products"
                    class="field-input !rounded-none !border-[rgba(120,0,0,0.10)] !bg-white/70 !px-3 !py-2.5 !text-sm"
                >
                <select name="sort" class="field-select !rounded-none !border-[rgba(120,0,0,0.10)] !bg-white/70 !px-3 !py-2.5 !text-sm">
                    <option value="">Newest first</option>
                    <option value="price_low" @selected($applied['sort'] === 'price_low')>Price: low to high</option>
                    <option value="price_high" @selected($applied['sort'] === 'price_high')>Price: high to low</option>
                    <option value="name" @selected($applied['sort'] === 'name')>Name</option>
                </select>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="button-primary flex-1 !rounded-[var(--radius-lg)] !px-4 !py-3 !text-sm">Apply</button>
            <a href="{{ $clearUrl }}" class="button-ghost !rounded-[var(--radius-lg)] !px-4 !py-3 !text-sm">Reset</a>
        </div>
    </div>
</form>
