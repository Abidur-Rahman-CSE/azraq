@php
    use Illuminate\Support\Str;

    $variantGroups = $variantGroups ?? collect();
    $simpleVariants = $simpleVariants ?? collect();
    $swatchMap = [
        'black' => '#1a1a1a',
        'pine' => '#A0784A',
        'natural pine wood' => '#A0784A',
        'gold' => '#C4A882',
        'antique gold' => '#C4A882',
        'white' => '#F5F5F0',
        'brown' => '#6B4226',
    ];
@endphp

@if ($variantGroups->isNotEmpty() || $simpleVariants->isNotEmpty())
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-medium uppercase tracking-[0.16em] text-[var(--text-muted)]">Choose a variant</h2>
            @if ($simpleVariants->isNotEmpty())
                <span class="text-xs font-medium text-[var(--text-main)]" x-text="activeVariant?.label || activeVariant?.name || '—'">—</span>
            @endif
        </div>

        @if ($variantGroups->isNotEmpty())
            @foreach ($variantGroups as $group)
                <div class="mb-4">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-xs font-medium uppercase tracking-[0.12em] text-[var(--text-muted)]">{{ $group['name'] }}:</span>
                        <span class="text-xs font-medium text-[var(--text-main)]" x-text="selectedVariants['{{ $group['key'] }}'] || '—'">—</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($group['values'] as $value)
                            @php
                                $label = $value['label'] ?? 'Option';
                                $variantId = $value['variant_id'] ?? null;
                                $available = (bool) ($value['available'] ?? true);
                                $swatchKey = Str::lower($value['swatch'] ?? $label);
                                $swatchColor = $swatchMap[$swatchKey] ?? '#CFC6BB';
                            @endphp

                            @if (Str::lower($group['name']) === 'frame type')
                                <button
                                    type="button"
                                    class="flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs transition-all duration-200 ease-out"
                                    title="{{ $available ? ($value['tooltip'] ?? $label) : 'Out of stock' }}"
                                    @click="selectVariant('{{ $group['key'] }}', '{{ $label }}', {{ $variantId ? '\''.$variantId.'\'' : 'null' }})"
                                    :class="selectedVariants['{{ $group['key'] }}'] === '{{ $label }}'
                                        ? 'border-[var(--accent-primary)] bg-[var(--pill-bg)] text-[var(--text-main)]'
                                        : '{{ $available ? 'border-[var(--border-soft)] text-[var(--text-main)] hover:border-[var(--accent-primary)]' : 'border-[var(--border-soft)] bg-[var(--bg-section-soft)] text-[var(--text-muted)] opacity-60 cursor-not-allowed' }}'"
                                    @disabled(! $available)
                                >
                                    <span class="h-2.5 w-2.5 rounded-full border border-black/10" style="background-color: {{ $swatchColor }}"></span>
                                    <span>{{ $label }}</span>
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="rounded-lg border px-3 py-1.5 text-sm transition-all duration-200 ease-out"
                                    title="{{ $available ? ($value['tooltip'] ?? $label) : 'Out of stock' }}"
                                    @click="selectVariant('{{ $group['key'] }}', '{{ $label }}', {{ $variantId ? '\''.$variantId.'\'' : 'null' }})"
                                    :class="selectedVariants['{{ $group['key'] }}'] === '{{ $label }}'
                                        ? 'border-[var(--accent-primary)] bg-[var(--accent-primary)] text-white'
                                        : '{{ $available ? 'border-[var(--border-soft)] text-[var(--text-main)] hover:border-[var(--accent-primary)]' : 'border-[var(--border-soft)] bg-[var(--bg-section-soft)] text-[var(--text-muted)] opacity-60 cursor-not-allowed' }}'"
                                    @disabled(! $available)
                                >
                                    {{ $label }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    @if (Str::lower($group['name']) === 'frame size')
                        <button type="button" class="mt-3 text-xs text-[var(--accent-primary)] underline transition duration-200 ease-out hover:text-[var(--accent-primary-hover)]" @click="openSizeGuide()">
                            Frame size guide →
                        </button>
                    @endif

                    <input type="hidden" name="selected_variants[{{ $group['key'] }}]" :value="selectedVariants['{{ $group['key'] }}'] || ''">
                </div>
            @endforeach
            <input type="hidden" name="variant_id" :value="selectedVariant || ''">
        @elseif ($simpleVariants->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach ($simpleVariants as $variant)
                    <label class="cursor-pointer">
                        <input type="radio" name="variant_id" value="{{ $variant['id'] }}" class="sr-only" x-model="selectedVariant">
                        <span
                            class="inline-flex rounded-lg border px-3 py-1.5 text-sm transition-all duration-200 ease-out"
                            :class="selectedVariant === '{{ $variant['id'] }}'
                                ? 'border-[var(--accent-primary)] bg-[var(--accent-primary)] text-white'
                                : '{{ $variant['available'] ? 'border-[var(--border-soft)] text-[var(--text-main)] hover:border-[var(--accent-primary)]' : 'border-[var(--border-soft)] bg-[var(--bg-section-soft)] text-[var(--text-muted)] opacity-60' }}'"
                            title="{{ $variant['available'] ? $variant['label'] : 'Out of stock' }}"
                        >
                            {{ $variant['label'] }}
                        </span>
                    </label>
                @endforeach
            </div>
        @endif

        @error('variant_id')
            <p class="text-[11px] text-[var(--color-danger)]">{{ $message }}</p>
        @enderror
    </div>
@endif
