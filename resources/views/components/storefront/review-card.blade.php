@props(['review'])

<article class="review-card-editorial">
    <p class="relative z-10 text-base leading-8 text-[var(--text-main)] mt-4">{{ $review->body }}</p>

    <div class="mt-6 flex items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-[var(--accent-secondary)]">{{ $review->author_name }}</p>
            @if ($review->title)
                <p class="mt-0.5 text-xs text-[var(--text-muted)]">{{ $review->title }}</p>
            @endif
        </div>
        <p class="text-[var(--azraq-burgundy)] tracking-wide text-sm">{{ str_repeat('★', $review->rating) }}<span class="text-[var(--text-muted)] opacity-20">{{ str_repeat('★', 5 - $review->rating) }}</span></p>
    </div>
</article>
