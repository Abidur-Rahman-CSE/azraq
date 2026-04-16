@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div class="max-w-3xl">
    @if ($eyebrow)
        <span class="eyebrow">{{ $eyebrow }}</span>
    @endif

    <h2 class="mt-5 max-w-3xl text-4xl font-semibold tracking-[-0.03em] text-[var(--text-main)] sm:text-5xl">{{ $title }}</h2>

    @if ($description)
        <p class="mt-5 max-w-2xl text-base leading-8 text-[var(--text-muted)] sm:text-lg">{{ $description }}</p>
    @endif
</div>
