@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'centered' => false,
    'dark' => false,
])

<div @class(['max-w-3xl', 'mx-auto text-center' => $centered])>
    @if ($eyebrow)
        <span @class(['section-kicker', 'section-kicker-light' => $dark])>{{ $eyebrow }}</span>
    @endif

    <h2 @class([
        'mt-5 max-w-3xl text-4xl font-semibold tracking-[-0.03em] sm:text-5xl',
        'text-[var(--text-main)]' => !$dark,
        'text-white' => $dark,
        'mx-auto' => $centered,
    ])>{{ $title }}</h2>

    @if ($description)
        <p @class([
            'mt-5 max-w-2xl text-base leading-8 sm:text-lg',
            'text-[var(--text-muted)]' => !$dark,
            'text-white/60' => $dark,
            'mx-auto' => $centered,
        ])>{{ $description }}</p>
    @endif
</div>
