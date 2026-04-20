@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div class="admin-page-header">
    <div>
        @if ($eyebrow)
            <p class="admin-page-header__eyebrow">{{ $eyebrow }}</p>
        @endif

        <h2 class="admin-page-header__title">{{ $title }}</h2>

        @if ($description)
            <p class="admin-page-header__description">{{ $description }}</p>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex flex-wrap items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
