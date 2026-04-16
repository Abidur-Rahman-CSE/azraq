@php($isEdit = $page->exists)
<form method="POST" action="{{ $isEdit ? route('admin.content.pages.update', $page) : route('admin.content.pages.store') }}" class="surface-card grid gap-6 p-6">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <div class="grid gap-6 md:grid-cols-2">
        <label class="space-y-2"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Title</span><input type="text" name="title" value="{{ old('title', $page->title) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"></label>
        <label class="space-y-2"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Slug</span><input type="text" name="slug" value="{{ old('slug', $page->slug) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"></label>
    </div>
    <label class="space-y-2"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Body</span><textarea name="body" rows="8" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">{{ old('body', $page->body) }}</textarea></label>
    <div class="grid gap-6 md:grid-cols-2">
        <label class="space-y-2"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta title</span><input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"></label>
        <label class="space-y-2"><span class="text-sm font-medium text-[var(--color-secondary-900)]">Meta description</span><input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"></label>
    </div>
    <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">Published</label>
    <div><button type="submit" class="button-primary">{{ $isEdit ? 'Save page' : 'Create page' }}</button></div>
</form>
