@php($isEdit = $tag->exists)

<form method="POST" action="{{ $isEdit ? route('admin.catalog.tags.update', $tag) : route('admin.catalog.tags.store') }}" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Name</span>
            <input type="text" name="name" value="{{ old('name', $tag->name) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Slug</span>
            <input type="text" name="slug" value="{{ old('slug', $tag->slug) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2 md:col-span-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Description</span>
            <textarea name="description" rows="4" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">{{ old('description', $tag->description) }}</textarea>
        </label>

        <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $tag->is_active)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
            Active
        </label>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="button-primary">{{ $isEdit ? 'Save changes' : 'Create tag' }}</button>
        <a href="{{ route('admin.catalog.tags.index') }}" class="button-ghost">Cancel</a>
    </div>
</form>
