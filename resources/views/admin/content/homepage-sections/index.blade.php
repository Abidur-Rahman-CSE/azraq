<x-layouts.admin title="Homepage Sections | Azraq Bridal">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Content</p>
            <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Homepage sections</h2>
        </div>
        <div class="grid gap-4">
            @foreach ($sections as $section)
                @php($settings = $section->settings ?? [])
                <article class="surface-card flex items-center justify-between gap-4 p-6">
                    <div>
                        <p class="text-sm font-medium text-[var(--color-primary-900)]">{{ $section->subtitle ?: $section->section_key }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-[var(--color-secondary-900)]">{{ $section->title }}</h3>
                        <p class="mt-2 text-sm text-[var(--color-text-soft)]">{{ $section->content }}</p>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs text-[var(--color-text-soft)]">
                            @if ($section->section_key === 'hero' && filled(data_get($settings, 'desktop_image_url')))
                                <span class="rounded-full bg-[var(--color-surface-cream)] px-3 py-1">Hero image ready</span>
                            @endif
                            @if ($section->section_key === 'featured_collections')
                                <span class="rounded-full bg-[var(--color-surface-cream)] px-3 py-1">{{ count(data_get($settings, 'selected_collection_ids', [])) ?: 0 }} collection picks</span>
                            @endif
                            @if ($section->section_key === 'featured_products')
                                <span class="rounded-full bg-[var(--color-surface-cream)] px-3 py-1">{{ count(data_get($settings, 'selected_product_ids', [])) ?: 0 }} product picks</span>
                            @endif
                            @if ($section->section_key === 'featured_categories')
                                <span class="rounded-full bg-[var(--color-surface-cream)] px-3 py-1">{{ count(data_get($settings, 'selected_category_ids', [])) ?: 0 }} category picks</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('admin.content.homepage-sections.edit', $section) }}" class="button-ghost">Edit</a>
                </article>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
