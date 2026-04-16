<x-layouts.admin title="Reviews | Azraq Bridal">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Reviews</p>
            <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Review moderation</h2>
        </div>
        <div class="grid gap-4">
            @foreach ($reviews as $review)
                <article class="surface-card p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-[var(--color-primary-900)]">{{ $review->product?->name }}</p>
                            <h3 class="mt-2 text-xl font-semibold text-[var(--color-secondary-900)]">{{ $review->title }}</h3>
                            <p class="mt-2 text-sm text-[var(--color-text-soft)]">{{ $review->author_name }} · {{ str_repeat('★', $review->rating) }}</p>
                            <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">{{ $review->body }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.reviews.update', $review) }}" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_approved" value="{{ $review->is_approved ? 0 : 1 }}">
                            <button type="submit" class="button-ghost">{{ $review->is_approved ? 'Unpublish' : 'Approve' }}</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
        {{ $reviews->links() }}
    </div>
</x-layouts.admin>
