<x-layouts.admin title="Homepage Sections | Azraq Bridal">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Content</p>
            <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Homepage sections</h2>
        </div>
        <div class="grid gap-4">
            @foreach ($sections as $section)
                <article class="surface-card flex items-center justify-between gap-4 p-6">
                    <div>
                        <p class="text-sm font-medium text-[var(--color-primary-900)]">{{ $section->subtitle ?: $section->section_key }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-[var(--color-secondary-900)]">{{ $section->title }}</h3>
                        <p class="mt-2 text-sm text-[var(--color-text-soft)]">{{ $section->content }}</p>
                    </div>
                    <a href="{{ route('admin.content.homepage-sections.edit', $section) }}" class="button-ghost">Edit</a>
                </article>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
