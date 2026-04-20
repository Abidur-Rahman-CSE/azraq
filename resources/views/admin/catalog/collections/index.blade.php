<x-layouts.admin
    title="Collections | Azraq Bridal"
    page-title="Collections"
    page-subtitle="Catalog workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Catalog'],
        ['label' => 'Collections'],
    ]"
>
    @php
        $activeFilterCount = collect($filters)->filter(fn ($value) => filled($value) || $value === true)->count();
    @endphp

    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Collection management"
            title="Collections now support cover imagery, mode, and product assignment."
            description="This upgraded collections view is built for merchandising work: cover readiness, featured state, manual versus automatic mode, and assigned product volume all surface in one fast-scanning list."
        >
            <x-slot:actions>
                <a href="{{ route('admin.catalog.collections.create') }}" class="button-primary">New collection</a>
            </x-slot:actions>
        </x-admin.page-header>

        <section class="grid gap-5 md:grid-cols-3">
            <article class="admin-alert-card surface-card {{ $stats['missing_covers'] > 0 ? 'admin-alert-card--warning' : 'admin-alert-card--success' }}">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Collections without cover image</p>
                <p class="admin-alert-card__value">{{ number_format($stats['missing_covers']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">These collections still need a cover visual for listing and homepage spotlight use.</p>
            </article>
            <article class="admin-alert-card surface-card admin-alert-card--success">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Manual collections</p>
                <p class="admin-alert-card__value">{{ number_format($stats['manual_collections']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">Curated sets currently managed by explicit product assignment.</p>
            </article>
            <article class="admin-alert-card surface-card admin-alert-card--success">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Featured collections</p>
                <p class="admin-alert-card__value">{{ number_format($stats['featured_collections']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">Collections currently marked for premium merchandising placement.</p>
            </article>
        </section>

        <section class="surface-card p-6">
            <form method="GET" action="{{ route('admin.catalog.collections.index') }}" class="grid gap-4 lg:grid-cols-[1.3fr_0.8fr_0.8fr_0.8fr]">
                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Search</span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search title or slug" class="field-input">
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Collection mode</span>
                    <select name="mode" class="field-select">
                        <option value="">Any mode</option>
                        <option value="manual" @selected($filters['mode'] === 'manual')>Manual</option>
                        <option value="automatic" @selected($filters['mode'] === 'automatic')>Automatic</option>
                    </select>
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

                <div class="lg:col-span-full flex flex-wrap items-center justify-between gap-3 pt-2">
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="button-primary">Apply filters</button>
                        <a href="{{ route('admin.catalog.collections.index') }}" class="button-ghost">Reset</a>
                    </div>
                    <p class="text-sm text-[var(--color-text-soft)]">
                        {{ $activeFilterCount > 0 ? $activeFilterCount.' active filters' : 'No filters applied' }}
                    </p>
                </div>
            </form>
        </section>

        <section class="space-y-4">
            @forelse ($collections as $collection)
                <article class="surface-card p-5 lg:p-6">
                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_repeat(5,minmax(0,0.65fr))] xl:items-center">
                        <div class="flex items-start gap-4">
                            <div class="h-24 w-24 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)]">
                                @if ($collection->cover_image_url)
                                    <img src="{{ $collection->cover_image_url }}" alt="{{ $collection->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center px-3 text-center text-xs font-semibold uppercase tracking-[0.14em] text-[var(--color-text-soft)]">No cover</div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">{{ $collection->name }}</h3>
                                    <span class="inline-flex rounded-full bg-[rgba(0,48,73,0.08)] px-3 py-1 text-xs font-semibold text-[var(--color-secondary-900)]">
                                        {{ $collection->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                    @if ($collection->is_featured)
                                        <span class="inline-flex rounded-full bg-[rgba(193,18,31,0.1)] px-3 py-1 text-xs font-semibold text-[var(--color-primary-900)]">Featured</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-[var(--color-text-soft)]">{{ $collection->slug }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="info-pill">{{ ucfirst($collection->collection_mode) }} mode</span>
                                    @if (! $collection->cover_image_url)
                                        <span class="info-pill !bg-[rgba(197,122,0,0.12)] !text-[var(--color-warning)]">Cover missing</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Products</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $collection->products_count }}</p>
                            <p class="mt-1 text-sm text-[var(--color-text-soft)]">{{ $collection->products_count > 0 ? 'Assigned items' : 'No products yet' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Mode</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ ucfirst($collection->collection_mode) }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Sort</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $collection->sort_order }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">CTA</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $collection->cta_label ?: 'Default CTA' }}</p>
                        </div>

                        <div class="flex flex-wrap items-center justify-start gap-3 xl:justify-end">
                            <a href="{{ route('admin.catalog.collections.edit', $collection) }}" class="button-ghost">Edit</a>
                            <form method="POST" action="{{ route('admin.catalog.collections.destroy', $collection) }}" onsubmit="return confirm('Delete this collection?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button-ghost !border-[rgba(193,18,31,0.18)] !text-[var(--color-danger)]">Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <section class="surface-card p-10 text-center">
                    <p class="text-2xl font-semibold text-[var(--color-secondary-900)]">No collections matched this filter set.</p>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Try broadening the filters or create a new collection with a cover image and curated products.</p>
                </section>
            @endforelse
        </section>

        {{ $collections->links() }}
    </div>
</x-layouts.admin>
