@php($isEdit = $faq->exists)
<form method="POST" action="{{ $isEdit ? route('admin.content.faqs.update', $faq) : route('admin.content.faqs.store') }}" class="surface-card grid gap-6 p-6">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <label class="space-y-2">
        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Question</span>
        <input type="text" name="question" value="{{ old('question', $faq->question) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
    </label>
    <label class="space-y-2">
        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Answer</span>
        <textarea name="answer" rows="5" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">{{ old('answer', $faq->answer) }}</textarea>
    </label>
    <div class="grid gap-6 md:grid-cols-2">
        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Sort order</span>
            <input type="number" name="sort_order" value="{{ old('sort_order', $faq->sort_order) }}" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
        </label>
        <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $faq->is_published)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
            Published
        </label>
    </div>
    <div><button type="submit" class="button-primary">{{ $isEdit ? 'Save FAQ' : 'Create FAQ' }}</button></div>
</form>
