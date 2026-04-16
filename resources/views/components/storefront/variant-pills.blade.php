@props(['variants'])

<div class="flex flex-wrap gap-3">
    @foreach ($variants as $variant)
        <label class="cursor-pointer">
            <input
                type="radio"
                name="variant_id"
                value="{{ $variant->id }}"
                class="peer sr-only"
                @checked(old('variant_id', $variants->firstWhere('is_default', true)?->id) == $variant->id)
            >
            <span class="inline-flex rounded-full border border-[var(--color-border-soft)] px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)] transition peer-checked:border-[var(--color-primary-900)] peer-checked:bg-[var(--color-surface-cream)]">
                {{ $variant->name }}
            </span>
        </label>
    @endforeach
</div>
