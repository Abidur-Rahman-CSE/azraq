<x-layouts.admin title="Pages | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div><p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Content</p><h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Pages</h2></div>
            <a href="{{ route('admin.content.pages.create') }}" class="button-primary">New page</a>
        </div>
        <div class="grid gap-4">
            @foreach ($pages as $page)
                <article class="surface-card flex items-center justify-between gap-4 p-6">
                    <div><h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">{{ $page->title }}</h3><p class="mt-2 text-sm text-[var(--color-text-soft)]">/{{ $page->slug }}</p></div>
                    <a href="{{ route('admin.content.pages.edit', $page) }}" class="button-ghost">Edit</a>
                </article>
            @endforeach
        </div>
        {{ $pages->links() }}
    </div>
</x-layouts.admin>
