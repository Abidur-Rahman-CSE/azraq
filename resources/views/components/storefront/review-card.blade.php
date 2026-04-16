@props(['review'])

<article class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/70 p-5">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $review->author_name }}</p>
        <p class="text-sm text-[var(--color-primary-900)]">{{ str_repeat('★', $review->rating) }}</p>
    </div>
    <h3 class="mt-3 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $review->title }}</h3>
    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">{{ $review->body }}</p>
</article>
