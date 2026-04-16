<x-layouts.admin title="Edit Homepage Section | Azraq Bridal">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Content</p>
            <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Edit homepage section</h2>
        </div>
        <form method="POST" action="{{ route('admin.content.homepage-sections.update', $section) }}" class="surface-card grid gap-6 p-6 md:grid-cols-2">
            @csrf
            @method('PUT')
            <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Title</span>
                <input type="text" name="title" value="{{ old('title', $section->title) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Subtitle</span>
                <input type="text" name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
            </label>
            <label class="space-y-2 md:col-span-2">
                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Content</span>
                <textarea name="content" rows="4" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">{{ old('content', $section->content) }}</textarea>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--color-secondary-900)]">CTA label</span>
                <input type="text" name="cta_label" value="{{ old('cta_label', $section->cta_label) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--color-secondary-900)]">CTA href</span>
                <input type="text" name="cta_href" value="{{ old('cta_href', $section->cta_href) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Sort order</span>
                <input type="number" name="sort_order" value="{{ old('sort_order', $section->sort_order) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
            </label>
            <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
                <input type="hidden" name="is_enabled" value="0">
                <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $section->is_enabled)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                Enabled
            </label>
            <div class="md:col-span-2">
                <button type="submit" class="button-primary">Save section</button>
            </div>
        </form>
    </div>
</x-layouts.admin>
