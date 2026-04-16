@php($isEdit = $category->exists)

<form method="POST" action="{{ $isEdit ? route('admin.catalog.categories.update', $category) : route('admin.catalog.categories.store') }}" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Name</span>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
            @error('name') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Slug</span>
            <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Parent category</span>
            <select name="parent_id" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
                <option value="">None</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parent->id)>{{ $parent->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Sort order</span>
            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2 md:col-span-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Description</span>
            <textarea name="description" rows="4" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">{{ old('description', $category->description) }}</textarea>
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta title</span>
            <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta description</span>
            <input type="text" name="meta_description" value="{{ old('meta_description', $category->meta_description) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
            Active
        </label>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="button-primary">{{ $isEdit ? 'Save changes' : 'Create category' }}</button>
        <a href="{{ route('admin.catalog.categories.index') }}" class="button-ghost">Cancel</a>
    </div>
</form>
