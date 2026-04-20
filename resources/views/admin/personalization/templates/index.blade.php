<x-layouts.admin
    title="Personalization Templates | Azraq Bridal"
    page-title="Personalization templates"
    page-subtitle="Personalization workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Personalization'],
        ['label' => 'Templates'],
    ]"
>
    @php
        $activeFilterCount = collect($filters)->filter(fn ($value) => filled($value))->count();
    @endphp

    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Nikah template manager"
            title="Template manager for base artwork, mask readiness, and sample preview data."
            description="This upgraded template index is built to surface gaps before products go live: missing base artwork, missing fields, and missing mockup coverage are visible immediately instead of being discovered later in proofing."
        >
            <x-slot:actions>
                <a href="{{ route('admin.personalization.templates.create') }}" class="button-primary">New template</a>
            </x-slot:actions>
        </x-admin.page-header>

        <section class="grid gap-5 md:grid-cols-3">
            <article class="admin-alert-card surface-card {{ $stats['missing_base_image'] > 0 ? 'admin-alert-card--warning' : 'admin-alert-card--success' }}">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Templates missing base image</p>
                <p class="admin-alert-card__value">{{ number_format($stats['missing_base_image']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">These templates still need a flat certificate base asset for live personalization work.</p>
            </article>
            <article class="admin-alert-card surface-card {{ $stats['missing_fields'] > 0 ? 'admin-alert-card--warning' : 'admin-alert-card--success' }}">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Templates missing fields</p>
                <p class="admin-alert-card__value">{{ number_format($stats['missing_fields']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">Templates without field definitions should not be considered storefront-ready.</p>
            </article>
            <article class="admin-alert-card surface-card {{ $stats['missing_mockups'] > 0 ? 'admin-alert-card--warning' : 'admin-alert-card--success' }}">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Templates missing mockups</p>
                <p class="admin-alert-card__value">{{ number_format($stats['missing_mockups']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">Mockup assignment is still pending for these templates and will be completed in the next admin phase.</p>
            </article>
        </section>

        <section class="surface-card p-6">
            <form method="GET" action="{{ route('admin.personalization.templates.index') }}" class="grid gap-4 lg:grid-cols-[1.3fr_0.9fr_0.9fr]">
                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Search</span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search template or product" class="field-input">
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Product</span>
                    <select name="product_id" class="field-select">
                        <option value="">All products</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) $filters['product_id'] === (string) $product->id)>{{ $product->name }}</option>
                        @endforeach
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

                <div class="lg:col-span-full flex flex-wrap items-center justify-between gap-3 pt-2">
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="button-primary">Apply filters</button>
                        <a href="{{ route('admin.personalization.templates.index') }}" class="button-ghost">Reset</a>
                    </div>
                    <p class="text-sm text-[var(--color-text-soft)]">
                        {{ $activeFilterCount > 0 ? $activeFilterCount.' active filters' : 'No filters applied' }}
                    </p>
                </div>
            </form>
        </section>

        <section class="space-y-4">
            @forelse ($templates as $template)
                <article class="surface-card p-5 lg:p-6">
                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_repeat(5,minmax(0,0.62fr))] xl:items-center">
                        <div class="flex items-start gap-4">
                            <div class="h-24 w-24 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)]">
                                @if ($template->thumbnail_image_url ?: $template->preview_image_url)
                                    <img src="{{ $template->thumbnail_image_url ?: $template->preview_image_url }}" alt="{{ $template->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center px-3 text-center text-xs font-semibold uppercase tracking-[0.14em] text-[var(--color-text-soft)]">No preview</div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">{{ $template->name }}</h3>
                                    <span class="inline-flex rounded-full bg-[rgba(0,48,73,0.08)] px-3 py-1 text-xs font-semibold text-[var(--color-secondary-900)]">
                                        {{ $template->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-[var(--color-text-soft)]">{{ $template->product?->name ?: 'No assigned product' }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if (! $template->base_template_url)
                                        <span class="info-pill !bg-[rgba(197,122,0,0.12)] !text-[var(--color-warning)]">Base image missing</span>
                                    @endif
                                    @if (! $template->mask_image_url)
                                        <span class="info-pill">Mask optional</span>
                                    @endif
                                    @if (($template->mockups_count ?? 0) === 0)
                                        <span class="info-pill !bg-[rgba(197,122,0,0.12)] !text-[var(--color-warning)]">Mockups pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Fields</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $template->fields_count }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Fonts</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $template->fonts_count }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Mockups</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $template->mockups_count ?? 0 }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Proof helper</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $template->proof_note_label ?: 'Default label' }}</p>
                        </div>

                        <div class="flex flex-wrap items-center justify-start gap-3 xl:justify-end">
                            <a href="{{ route('admin.personalization.templates.edit', $template) }}" class="button-ghost">Edit</a>
                            <form method="POST" action="{{ route('admin.personalization.templates.duplicate', $template) }}">
                                @csrf
                                <button type="submit" class="button-ghost">Duplicate</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <section class="surface-card p-10 text-center">
                    <p class="text-2xl font-semibold text-[var(--color-secondary-900)]">No templates matched this filter set.</p>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Try broadening the filters or create a new template with base artwork and sample preview data.</p>
                </section>
            @endforelse
        </section>

        {{ $templates->links() }}
    </div>
</x-layouts.admin>
