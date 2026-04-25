@php
    $variantGroups = $variantGroups ?? collect();
    $simpleVariants = $simpleVariants ?? collect();
@endphp

@if ($variantGroups->isNotEmpty() || $simpleVariants->isNotEmpty())
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-medium uppercase tracking-[0.16em] text-[var(--text-muted)]">Choose a variant</h2>
            <span class="text-xs font-medium text-[var(--text-main)]" x-text="activeVariant?.label || activeVariant?.name || '—'">—</span>
        </div>

        <template x-if="hasGroupedVariants">
            <div class="space-y-7">
                <template x-for="group in variantGroups" :key="group.key">
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                            <span class="font-serif text-[1.15rem] font-semibold uppercase tracking-[0.04em] text-[var(--accent-secondary)]" x-text="`${group.name}:`"></span>
                            <span class="font-serif text-[1.15rem] font-semibold tracking-[0.02em] text-[var(--accent-secondary)]" x-text="selectedValueLabel(group.key)"></span>
                        </div>

                        <div class="flex flex-wrap gap-4" x-show="visibleValuesForGroup(group.key).length > 0">
                            <template x-for="value in visibleValuesForGroup(group.key)" :key="`${group.key}-${value.value}`">
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded-full border px-8 py-3 text-[0.95rem] font-medium transition-all duration-200 ease-out"
                                    @click="selectVariant(group.key, value.value, value.variant_id ?? null)"
                                    :class="selectedVariants[group.key] === value.value
                                        ? 'border-[var(--accent-secondary)] bg-[var(--accent-secondary)] text-white shadow-[0_12px_30px_rgba(0,48,73,0.14)]'
                                        : 'border-[rgba(0,48,73,0.14)] bg-transparent text-[var(--accent-secondary)] hover:border-[var(--accent-secondary)]'"
                                    :title="value.tooltip || value.label"
                                >
                                    <span
                                        x-show="frameTypeChip(group)"
                                        class="h-2.5 w-2.5 rounded-full border border-black/10"
                                        :style="`background:${swatchColor(value)}`"
                                    ></span>
                                    <span x-text="value.label"></span>
                                </button>
                            </template>
                        </div>

                        <button
                            type="button"
                            class="text-sm font-medium text-[var(--color-success)] transition duration-200 ease-out hover:underline"
                            x-show="group.key === 'frame_size'"
                            @click="openSizeGuide()"
                        >
                            Frame size guide →
                        </button>

                        <input type="hidden" :name="`selected_variants[${group.key}]`" :value="selectedVariants[group.key] || ''">
                    </div>
                </template>

                <input type="hidden" name="variant_id" :value="selectedVariant || ''">
            </div>
        </template>

        <template x-if="!hasGroupedVariants && variants.length">
            <div class="flex flex-wrap gap-4">
                <template x-for="variant in variants" :key="variant.id">
                    <label class="cursor-pointer">
                        <input type="radio" name="variant_id" :value="variant.id" class="sr-only" x-model="selectedVariant">
                        <span
                            class="inline-flex rounded-full border px-8 py-3 text-[0.95rem] font-medium transition-all duration-200 ease-out"
                            :class="selectedVariant === `${variant.id}`
                                ? 'border-[var(--accent-secondary)] bg-[var(--accent-secondary)] text-white shadow-[0_12px_30px_rgba(0,48,73,0.14)]'
                                : 'border-[rgba(0,48,73,0.14)] bg-transparent text-[var(--accent-secondary)] hover:border-[var(--accent-secondary)]'"
                            :title="variant.label || variant.name"
                            x-text="variant.label || variant.name"
                        ></span>
                    </label>
                </template>
            </div>
        </template>

        @error('variant_id')
            <p class="text-[11px] text-[var(--color-danger)]">{{ $message }}</p>
        @enderror
    </div>
@endif
