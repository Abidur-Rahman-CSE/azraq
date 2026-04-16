@props(['value' => 1])

<label class="space-y-2">
    <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Quantity</span>
    <input type="number" min="1" max="20" name="quantity" value="{{ old('quantity', $value) }}" class="w-28 rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
</label>
