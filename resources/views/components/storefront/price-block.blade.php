@props(['product', 'variant' => null])

@php($price = (float) ($variant?->price ?: $product->price))
@php($compareAt = $variant?->compare_at_price ?: $product->compare_at_price)

<div class="space-y-2">
    <div class="flex items-end gap-3">
        <p class="text-3xl font-semibold text-[var(--color-secondary-900)]">BDT {{ number_format($price, 0) }}</p>
        @if ($compareAt)
            <p class="text-base text-[var(--color-text-soft)] line-through">BDT {{ number_format((float) $compareAt, 0) }}</p>
        @endif
    </div>
    <p class="text-sm text-[var(--color-text-soft)]">
        {{ $product->manage_stock ? 'Stock-aware checkout flow' : 'Made-to-order flow' }}
    </p>
</div>
