<x-layouts.admin
    title="Mockups | Azraq Bridal"
    page-title="Mockups"
    page-subtitle="Personalization workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Personalization'],
        ['label' => 'Mockups'],
    ]"
>
    @php
        $activeFilterCount = collect($filters)->filter(fn ($value) => filled($value))->count();
    @endphp

    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Nikah mockup manager"
            title="Lifestyle mockups, masks, and perspective mapping readiness in one workspace."
            description="This phase upgrades the placeholder into a real manager so we can audit mask coverage, see which mockups still need mapping, and confirm template coverage before the visual editor lands."
        >
            <x-slot:actions>
                <a href="{{ route('admin.personalization.templates.index') }}" class="button-ghost">Open templates</a>
                <a href="{{ route('admin.mockups.create') }}" class="button-primary">New mockup</a>
            </x-slot:actions>
        </x-admin.page-header>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="admin-alert-card surface-card {{ $stats['active'] > 0 ? 'admin-alert-card--success' : 'admin-alert-card--warning' }}">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Active mockups</p>
                <p class="admin-alert-card__value">{{ number_format($stats['active']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">Lifestyle frames currently available for approval-ready Nikah previews.</p>
            </article>
            <article class="admin-alert-card surface-card {{ $stats['missing_masks'] > 0 ? 'admin-alert-card--warning' : 'admin-alert-card--success' }}">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Missing masks</p>
                <p class="admin-alert-card__value">{{ number_format($stats['missing_masks']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">These mockups still need clipping support for cleaner compositing later.</p>
            </article>
            <article class="admin-alert-card surface-card {{ $stats['missing_mappings'] > 0 ? 'admin-alert-card--warning' : 'admin-alert-card--success' }}">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Missing mappings</p>
                <p class="admin-alert-card__value">{{ number_format($stats['missing_mappings']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">Perspective coordinates are still pending for these mockups.</p>
            </article>
            <article class="admin-alert-card surface-card {{ $stats['missing_overlays'] > 0 ? 'admin-alert-card--warning' : 'admin-alert-card--success' }}">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Missing overlays</p>
                <p class="admin-alert-card__value">{{ number_format($stats['missing_overlays']) }}</p>
                <p class="text-sm leading-7 text-[var(--color-text-soft)]">Optional top overlay layers for richer depth and frame realism.</p>
            </article>
        </section>

        <section class="surface-card p-6">
            <form method="GET" action="{{ route('admin.mockups.index') }}" class="grid gap-4 lg:grid-cols-[1.2fr_0.9fr_0.9fr_0.9fr]">
                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Search</span>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search mockup or template" class="field-input">
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Template</span>
                    <select name="template_id" class="field-select">
                        <option value="">All templates</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}" @selected((string) $filters['template_id'] === (string) $template->id)>{{ $template->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Render mode</span>
                    <select name="render_mode" class="field-select">
                        <option value="">Any mode</option>
                        <option value="flat_fit" @selected($filters['render_mode'] === 'flat_fit')>Flat fit</option>
                        <option value="perspective_quad" @selected($filters['render_mode'] === 'perspective_quad')>Perspective quad</option>
                        <option value="masked_perspective" @selected($filters['render_mode'] === 'masked_perspective')>Masked perspective</option>
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
                        <a href="{{ route('admin.mockups.index') }}" class="button-ghost">Reset</a>
                    </div>
                    <p class="text-sm text-[var(--color-text-soft)]">
                        {{ $activeFilterCount > 0 ? $activeFilterCount.' active filters' : 'No filters applied' }}
                    </p>
                </div>
            </form>
        </section>

        <section class="space-y-4">
            @forelse ($mockups as $mockup)
                <article class="surface-card p-5 lg:p-6">
                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_repeat(6,minmax(0,0.55fr))] xl:items-center">
                        <div class="flex items-start gap-4">
                            <div class="h-28 w-28 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)]">
                                @if ($mockup->thumb_image_url ?: $mockup->base_image_url)
                                    <img src="{{ $mockup->thumb_image_url ?: $mockup->base_image_url }}" alt="{{ $mockup->title }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center px-3 text-center text-xs font-semibold uppercase tracking-[0.14em] text-[var(--color-text-soft)]">No thumb</div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">{{ $mockup->title }}</h3>
                                    <span class="inline-flex rounded-full bg-[rgba(0,48,73,0.08)] px-3 py-1 text-xs font-semibold text-[var(--color-secondary-900)]">
                                        {{ $mockup->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-[var(--color-text-soft)]">
                                    {{ $mockup->template?->name ?: 'No template assigned' }}
                                    @if ($mockup->template?->product)
                                        <span class="text-[var(--color-border-strong)]">•</span>
                                        {{ $mockup->template->product->name }}
                                    @endif
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if (! $mockup->mask_image_url)
                                        <span class="info-pill !bg-[rgba(197,122,0,0.12)] !text-[var(--color-warning)]">Mask missing</span>
                                    @endif
                                    @if (! $mockup->map)
                                        <span class="info-pill !bg-[rgba(197,122,0,0.12)] !text-[var(--color-warning)]">Mapping pending</span>
                                    @endif
                                    @if (! $mockup->overlay_image_url)
                                        <span class="info-pill">Overlay optional</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Mode</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ str($mockup->render_mode)->headline() }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Sort</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $mockup->sort_order }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Mask</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $mockup->mask_image_url ? 'Ready' : 'Missing' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Overlay</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $mockup->overlay_image_url ? 'Ready' : 'Optional' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Mapping</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $mockup->map ? 'Mapped' : 'Pending' }}</p>
                        </div>

                        <div class="flex flex-wrap items-center justify-start gap-3 xl:justify-end">
                            <a href="{{ route('admin.mockups.edit', $mockup) }}" class="button-primary">Edit</a>
                            @if ($mockup->template)
                                <a href="{{ route('admin.personalization.templates.edit', $mockup->template) }}" class="button-ghost">Open template</a>
                            @endif
                            <form
                                method="POST"
                                action="{{ route('admin.mockups.destroy', $mockup) }}"
                                onsubmit="return confirm('Delete this mockup? This removes its mapping and product assignments too.');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button-ghost !border-[rgba(120,0,0,0.18)] !text-[var(--accent-primary)]" aria-label="Delete {{ $mockup->title }}">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <section class="surface-card p-10 text-center">
                    <p class="text-2xl font-semibold text-[var(--color-secondary-900)]">No mockups matched this filter set.</p>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Seed or upload lifestyle mockups to start building the Nikah render library.</p>
                </section>
            @endforelse
        </section>

        {{ $mockups->links() }}
    </div>
</x-layouts.admin>
