<x-layouts.admin
    title="Products | Azraq Bridal"
    page-title="Products"
    page-subtitle="Catalog workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Catalog'],
        ['label' => 'Products'],
    ]"
>
    @php
        $activeFilterCount = collect($filters)->filter(fn ($value) => filled($value) || $value === true)->count();
    @endphp

    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Catalog operations"
            title="Products index built for media, stock, and personalization scanning."
            description="This upgraded view surfaces thumbnail readiness, template linkage, stock state, and product-type filters so catalog work no longer depends on opening each product one by one."
        >
            <x-slot:actions>
                <a href="{{ route('admin.catalog.products.create') }}" class="button-primary">New product</a>
            </x-slot:actions>
        </x-admin.page-header>

        <section class="surface-card p-6">
            <form method="GET" action="{{ route('admin.catalog.products.index') }}" class="grid gap-4 lg:grid-cols-[1.2fr_repeat(4,minmax(0,1fr))]">
                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Search</span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search title, SKU, or slug" class="field-input">
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Product type</span>
                    <select name="type" class="field-select">
                        <option value="">All types</option>
                        @foreach ($productTypes as $type)
                            <option value="{{ $type['value'] }}" @selected($filters['type'] === $type['value'])>{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Category</span>
                    <select name="category_id" class="field-select">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Stock state</span>
                    <select name="stock_status" class="field-select">
                        <option value="">Any stock mode</option>
                        <option value="low" @selected($filters['stock_status'] === 'low')>Low stock</option>
                        <option value="out" @selected($filters['stock_status'] === 'out')>Out of stock</option>
                        <option value="made_to_order" @selected($filters['stock_status'] === 'made_to_order')>Made to order</option>
                    </select>
                </label>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                    <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <input type="checkbox" name="customizable" value="1" @checked($filters['customizable']) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Customizable only
                    </label>
                    <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <input type="checkbox" name="nikah_only" value="1" @checked($filters['nikah_only']) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Nikah only
                    </label>
                </div>

                <div class="lg:col-span-full flex flex-wrap items-center justify-between gap-3 pt-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="button-primary">Apply filters</button>
                        <a href="{{ route('admin.catalog.products.index') }}" class="button-ghost">Reset</a>
                    </div>
                    <p class="text-sm text-[var(--color-text-soft)]">
                        {{ $activeFilterCount > 0 ? $activeFilterCount.' active filters' : 'No filters applied' }}
                    </p>
                </div>
            </form>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
            @foreach ($productTypes as $type)
                <article class="surface-card p-5">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-primary-900)]">{{ $type['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold text-[var(--color-secondary-900)]">
                        {{ $products->getCollection()->filter(fn ($product) => $product->type?->value === $type['value'])->count() }}
                    </p>
                    <p class="mt-2 text-sm text-[var(--color-text-soft)]">Visible in the current result set.</p>
                </article>
            @endforeach
        </section>

        <section class="space-y-4">
            @forelse ($products as $product)
                @php
                    $thumbnail = $product->featured_image_url ?: $product->images->first()?->image_url;
                    $isLowStock = $product->manage_stock && $product->stock_quantity <= $product->low_stock_threshold;
                    $isOutOfStock = $product->manage_stock && $product->stock_quantity <= 0;
                @endphp

                <article class="surface-card p-5 lg:p-6">
                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_repeat(5,minmax(0,0.7fr))] xl:items-center">
                        <div class="flex items-start gap-4">
                            <div class="h-24 w-24 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)]">
                                @if ($thumbnail)
                                    <img src="{{ $thumbnail }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center px-3 text-center text-xs font-semibold uppercase tracking-[0.14em] text-[var(--color-text-soft)]">No image</div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">{{ $product->name }}</h3>
                                    <span class="inline-flex rounded-full bg-[rgba(0,48,73,0.08)] px-3 py-1 text-xs font-semibold text-[var(--color-secondary-900)]">{{ ucfirst($product->status) }}</span>
                                    @if ($product->is_featured)
                                        <span class="inline-flex rounded-full bg-[rgba(193,18,31,0.1)] px-3 py-1 text-xs font-semibold text-[var(--color-primary-900)]">Featured</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-[var(--color-text-soft)]">{{ $product->sku ?: 'No SKU' }} • {{ $product->slug }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="info-pill">{{ $product->type?->label() }}</span>
                                    <span class="info-pill">{{ $product->category?->name ?: 'No category' }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Price</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">BDT {{ number_format((float) $product->price, 2) }}</p>
                            @if ($product->compare_at_price)
                                <p class="mt-1 text-sm text-[var(--color-text-soft)]">Compare at BDT {{ number_format((float) $product->compare_at_price, 2) }}</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Stock</p>
                            @if ($product->manage_stock)
                                <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $product->stock_quantity }}</p>
                                <p class="mt-1 text-sm {{ $isOutOfStock ? 'text-[var(--color-danger)]' : ($isLowStock ? 'text-[var(--color-warning)]' : 'text-[var(--color-text-soft)]') }}">
                                    {{ $isOutOfStock ? 'Out of stock' : ($isLowStock ? 'Low stock alert' : 'Healthy stock') }}
                                </p>
                            @else
                                <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">Made to order</p>
                                <p class="mt-1 text-sm text-[var(--color-text-soft)]">Stock is not tracked.</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Media</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $product->images_count }}</p>
                            <p class="mt-1 text-sm text-[var(--color-text-soft)]">
                                {{ $thumbnail ? 'Thumbnail ready' : 'Needs featured image' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Personalization</p>
                            <p class="mt-2 text-sm font-semibold {{ $product->personalizationTemplate ? 'text-[var(--color-secondary-900)]' : 'text-[var(--color-text-soft)]' }}">
                                {{ $product->personalizationTemplate ? 'Template linked' : 'No template assigned' }}
                            </p>
                            <p class="mt-1 text-sm text-[var(--color-text-soft)]">
                                {{ $product->proof_notes_enabled ? 'Proof notes on' : 'Proof notes off' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center justify-start gap-3 xl:justify-end">
                            <a href="{{ route('admin.catalog.products.edit', $product) }}" class="button-ghost">Edit</a>
                            <form method="POST" action="{{ route('admin.catalog.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button-ghost !border-[rgba(193,18,31,0.18)] !text-[var(--color-danger)]">Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <section class="surface-card p-10 text-center">
                    <p class="text-2xl font-semibold text-[var(--color-secondary-900)]">No products matched this filter set.</p>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Try broadening the catalog filters or create a new product with the upgraded media-aware editor.</p>
                </section>
            @endforelse
        </section>

        {{ $products->links() }}
    </div>
</x-layouts.admin>
