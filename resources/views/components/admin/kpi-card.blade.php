@props([
    'label',
    'value',
    'description' => null,
])

<article class="admin-kpi-card surface-card">
    <p class="admin-kpi-card__label">{{ $label }}</p>
    <p class="admin-kpi-card__value">{{ $value }}</p>

    @if ($description)
        <p class="admin-kpi-card__description">{{ $description }}</p>
    @endif
</article>
