@props([
    'mdCols' => 3,
    'lgCols' => 4,
    'gap' => 'md',
])

@php
    $gapClass = match($gap) {
        'sm' => 'carousel-track--gap-sm',
        'lg' => 'carousel-track--gap-lg',
        default => 'carousel-track--gap-md',
    };
@endphp

<div class="carousel-rail" style="--carousel-md: {{ $mdCols }}; --carousel-lg: {{ $lgCols }};">
    <div class="carousel-track {{ $gapClass }}">
        {{ $slot }}
    </div>
</div>
