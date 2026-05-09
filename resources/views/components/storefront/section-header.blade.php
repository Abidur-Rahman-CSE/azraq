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
        'mt-3 max-w-3xl text-3xl font-semibold leading-[1.1] tracking-[-0.015em] sm:text-4xl lg:text-5xl',
        'text-[var(--text-main)]' => !$dark,
        'text-white' => $dark,
        'mx-auto' => $centered,
    ]) style="font-family: 'Cormorant Garamond', Georgia, serif;">{{ $title }}</h2>

    @if ($description)
        <p @class([
            'mt-4 max-w-2xl text-sm leading-7 sm:text-base sm:leading-8',
            'text-[var(--text-muted)]' => !$dark,
            'text-white/60' => $dark,
            'mx-auto' => $centered,
        ])>{{ $description }}</p>
    @endif
</div>
