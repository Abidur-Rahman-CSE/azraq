<x-layouts.admin
    title="Categories | Azraq Bridal"
    page-title="Categories"
    page-subtitle="Catalog workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Catalog'],
        ['label' => 'Categories'],
    ]"
>
    @php
        $activeFilterCount = collect($filters)->filter(fn ($value) => filled($value) || $value === true)->count();
    @endphp

    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Category management"
            title="Categories now track imagery, homepage readiness, and content quality."
            description="This upgraded index is built for quick merchandising decisions: thumbnail visibility, empty category detection, homepage flags, and banner readiness all sit in one scanning-friendly view."
        >
            <x-slot:actions>
                <a href="{{ route('admin.catalog.categories.create') }}" class="button-primary">New category</a>
            </x-slot:actions>
        </x-admin.page-header>

        <section class="grid gap-5 md:grid-cols-3">
            <article class="admin-alert-card surface-card {{ $stats['missing_images'] > 0 ? 'admin-alert-card--warning' : 'admin-alert-card--success' }}">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Categories without image</p>
                <p class="admin-alert-card__value">{{ number_format($stats['missing_images']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">These categories still need a lead image for cards and navigation visuals.</p>
            </article>
            <article class="admin-alert-card surface-card {{ $stats['missing_banners'] > 0 ? 'admin-alert-card--warning' : 'admin-alert-card--success' }}">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Categories without banner</p>
                <p class="admin-alert-card__value">{{ number_format($stats['missing_banners']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">Banner coverage is still missing for category landing pages and promo layouts.</p>
            </article>
            <article class="admin-alert-card surface-card {{ $stats['empty_categories'] > 0 ? 'admin-alert-card--warning' : 'admin-alert-card--success' }}">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Empty categories</p>
                <p class="admin-alert-card__value">{{ number_format($stats['empty_categories']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">These categories have no products attached yet and may confuse the storefront browse flow.</p>
            </article>
        </section>

        <section class="surface-card p-6">
            <form method="GET" action="{{ route('admin.catalog.categories.index') }}" class="grid gap-4 lg:grid-cols-[1.4fr_0.8fr_repeat(2,0.7fr)]">
                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Search</span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search title or slug" class="field-input">
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Status</span>
                    <select name="status" class="field-select">
                        <option value="">Any status</option>
                        <option value="active" @selected($filters['status'] === 'active')>Active</option>
                        <option value="hidden" @selected($filters['status'] === 'hidden')>Hidden</option>
                    </select>
                </label>

                <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                    <input type="checkbox" name="featured" value="1" @checked($filters['featured']) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                    Featured only
                </label>

                <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                    <input type="checkbox" name="homepage" value="1" @checked($filters['homepage']) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                    Homepage only
                </label>

                <div class="lg:col-span-full flex flex-wrap items-center justify-between gap-3 pt-2">
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="button-primary">Apply filters</button>
                        <a href="{{ route('admin.catalog.categories.index') }}" class="button-ghost">Reset</a>
                    </div>
                    <p class="text-sm text-[var(--color-text-soft)]">
                        {{ $activeFilterCount > 0 ? $activeFilterCount.' active filters' : 'No filters applied' }}
                    </p>
                </div>
            </form>
        </section>

        <section class="space-y-4">
            @forelse ($categories as $category)
                <article class="surface-card p-5 lg:p-6">
                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_repeat(5,minmax(0,0.65fr))] xl:items-center">
                        <div class="flex items-start gap-4">
                            <div class="h-24 w-24 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)]">
                                @if ($category->image_url)
                                    <img src="{{ $category->image_url }}" alt="{{ $category->alt_text ?: $category->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center px-3 text-center text-xs font-semibold uppercase tracking-[0.14em] text-[var(--color-text-soft)]">No image</div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">{{ $category->name }}</h3>
                                    <span class="inline-flex rounded-full bg-[rgba(0,48,73,0.08)] px-3 py-1 text-xs font-semibold text-[var(--color-secondary-900)]">
                                        {{ $category->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-[var(--color-text-soft)]">{{ $category->slug }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if ($category->is_featured)
                                        <span class="info-pill">Featured</span>
                                    @endif
                                    @if ($category->show_on_homepage)
                                        <span class="info-pill">Homepage</span>
                                    @endif
                                    @if (! $category->banner_image_url)
                                        <span class="info-pill !bg-[rgba(197,122,0,0.12)] !text-[var(--color-warning)]">Banner missing</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Parent</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $category->parent?->name ?? 'Primary' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Products</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $category->products_count }}</p>
                            <p class="mt-1 text-sm text-[var(--color-text-soft)]">{{ $category->products_count > 0 ? 'Assigned products' : 'Empty category' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Sort</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $category->sort_order }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Homepage</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $category->show_on_homepage ? 'Visible' : 'Hidden' }}</p>
                        </div>

                        <div class="flex flex-wrap items-center justify-start gap-3 xl:justify-end">
                            <a href="{{ route('admin.catalog.categories.edit', $category) }}" class="button-ghost">Edit</a>
                            <form method="POST" action="{{ route('admin.catalog.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button-ghost !border-[rgba(193,18,31,0.18)] !text-[var(--color-danger)]">Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <section class="surface-card p-10 text-center">
                    <p class="text-2xl font-semibold text-[var(--color-secondary-900)]">No categories matched this filter set.</p>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Try broadening the filters or add a new category with image and banner coverage.</p>
                </section>
            @endforelse
        </section>

        {{ $categories->links() }}
    </div>
</x-layouts.admin>
