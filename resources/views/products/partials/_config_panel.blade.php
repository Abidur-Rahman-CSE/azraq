@php
    $orderedFields = $template->fields
        ->sortBy(fn ($field) => (int) ($field->position ?? 0))
        ->values();
    $proofNoteLabel = $template->proof_note_label ?: 'Add special instructions';
@endphp

<section class="space-y-4 text-[var(--text-main)]">
    <div class="surface-card-featured p-5 sm:p-6">
        <div class="flex flex-wrap gap-2">
            @foreach ($badgeItems as $index => $badge)
                @php
                    $badgeClasses = match ($index) {
                        0 => 'bg-[var(--pill-bg)] text-[var(--accent-primary)]',
                        1 => 'bg-[rgba(120,0,0,0.08)] text-[var(--accent-primary)]',
                        default => 'bg-[rgba(0,48,73,0.08)] text-[var(--accent-secondary)]',
                    };
                @endphp
                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-medium {{ $badgeClasses }}">{{ $badge }}</span>
            @endforeach
        </div>

        <h1 class="mt-2 font-serif text-[26px] font-semibold leading-tight text-[var(--text-main)]">{{ $product->name }}</h1>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="text-2xl font-semibold text-[var(--accent-primary)]" x-text="formatMoney(displayPrice)">BDT {{ number_format((float) $product->price, 0) }}</span>
            @if ($product->compare_at_price)
                <span class="text-sm text-[var(--text-muted)] line-through" x-show="displayComparePrice" x-text="formatMoney(displayComparePrice)">BDT {{ number_format((float) $product->compare_at_price, 0) }}</span>
                <span class="rounded-full bg-[rgba(120,0,0,0.08)] px-2 py-0.5 text-xs font-medium text-[var(--accent-primary)]" x-show="savePercent > 0" x-text="`SAVE ${savePercent}%`"></span>
            @endif
        </div>

        <p class="mt-1 text-xs text-[var(--accent-soft)]">Custom proof included before printing</p>
        <p class="mt-2 text-sm leading-relaxed text-[var(--text-muted)]">{{ $shortDescription }}</p>
    </div>

    <form id="order-form" method="POST" action="{{ route('cart.store', $product) }}" class="space-y-4" x-ref="mainOrderForm" @submit="submitting = true">
        @csrf
        <input type="hidden" name="quantity" value="1">
        <input type="hidden" name="font_id" :value="primaryFontId()">
        <input type="hidden" name="proof_note" :value="proofNote">
        @if (($mockups instanceof \Illuminate\Support\Collection ? $mockups : collect($mockups ?? []))->isNotEmpty())
            <input type="hidden" name="mockup_id" :value="currentMockup?.id || ''">
        @endif

        @include('products.partials._variant_selectors', [
            'variantGroups' => $variantGroups,
            'simpleVariants' => $simpleVariants,
        ])

        <div class="my-5 flex items-center gap-3">
            <div class="h-px flex-1 bg-[var(--border-soft)]"></div>
            <span class="text-xs uppercase tracking-[0.3em] text-[var(--text-muted)]">Personalise</span>
            <div class="h-px flex-1 bg-[var(--border-soft)]"></div>
        </div>

        <div class="surface-card p-5">
            <div class="mb-4 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[rgba(120,0,0,0.08)] text-sm font-semibold text-[var(--accent-primary)]">1</span>
                <h2 class="text-base font-semibold text-[var(--text-main)]">Personalise your certificate</h2>
            </div>

            <div class="space-y-4">
                @foreach ($orderedFields as $field)
                    @php
                        $fieldName = 'personalization.'.$field->field_key;
                        $fieldKey = $field->field_key;
                        $isAutoDate = str($fieldKey)->endsWith('_bangla') || str($fieldKey)->endsWith('_arabic');
                        $isDate = !$isAutoDate && (str($fieldKey)->contains('date') || ($field->type ?? '') === 'date');
                        $fieldMax = $field->max_length ?: 100;
                    @endphp

                    {{-- Auto-computed date fields (Bangla/Arabic) — hidden, filled by computeAutoDates --}}
                    @if ($isAutoDate)
                        <input type="hidden" name="personalization[{{ $fieldKey }}]" :value="fields['{{ $fieldKey }}'] || ''">
                        @continue
                    @endif

                    <div class="field-group">
                        <div class="mb-1 flex items-baseline justify-between gap-3">
                            <label for="field-{{ $fieldKey }}" class="text-[10px] font-medium uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                {{ $field->label }}
                                @if ($field->is_required)
                                    <span class="text-[var(--accent-primary)]">*</span>
                                @endif
                            </label>
                            @if ($field->max_length)
                                <span class="text-[10px] text-[var(--text-muted)]" x-text="`${(fields['{{ $fieldKey }}'] || '').length}/{{ $field->max_length }}`">{{ strlen((string) old($fieldName, '')) }}/{{ $field->max_length }}</span>
                            @endif
                        </div>

                        @if ($isDate)
                            <input
                                id="field-{{ $fieldKey }}"
                                type="date"
                                name="personalization[{{ $fieldKey }}]"
                                value="{{ old($fieldName, $field->default_value ?? '') }}"
                                x-model="fields['{{ $fieldKey }}']"
                                @change="computeAutoDates('{{ $fieldKey }}', @js($field->settings ?? [])); renderPreview()"
                                class="field-input !rounded-[var(--radius-md)] !bg-[var(--bg-section-soft)] !px-3 !py-2.5 !text-sm"
                            >
                        @else
                            <input
                                id="field-{{ $fieldKey }}"
                                type="text"
                                name="personalization[{{ $fieldKey }}]"
                                value="{{ old($fieldName, $field->default_value ?? $field->preview_sample_value ?? '') }}"
                                x-model="fields['{{ $fieldKey }}']"
                                @input.debounce.150ms="renderPreview()"
                                placeholder="{{ $field->placeholder }}"
                                maxlength="{{ $fieldMax }}"
                                class="field-input !rounded-[var(--radius-md)] !bg-[var(--bg-section-soft)] !px-3 !py-2.5 !text-sm"
                            >
                        @endif

                        @if ($field->help_text)
                            <p class="mt-1 text-[10px] italic text-[var(--text-muted)]">{{ $field->help_text }}</p>
                        @endif

                        @error($fieldName)
                            <p class="mt-1 text-[11px] text-[var(--color-danger)]">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="surface-card p-5">
            <div class="mb-4 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[rgba(120,0,0,0.08)] text-sm font-semibold text-[var(--accent-primary)]">2</span>
                <h2 class="text-base font-semibold text-[var(--text-main)]">Choose a font</h2>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ($fonts as $font)
                    <button
                        type="button"
                        class="min-w-[96px] rounded-lg border p-3 text-left transition-all duration-200 ease-out"
                        :class="activeFont === '{{ $font->id }}' ? 'border-[var(--accent-primary)] bg-[var(--pill-bg)]' : 'border-[var(--border-soft)] hover:border-[var(--accent-primary)]'"
                        @click="applyNameFont('{{ $font->id }}')"
                        aria-label="Choose {{ $font->name }}"
                    >
                        <span class="block text-lg leading-none text-[var(--text-main)]" style="font-family: {{ $font->font_family ?: $font->css_font_family }};">
                            <span x-text="fields.groom_name || fields.groom || 'Ahmad Ali'">Ahmad Ali</span>
                        </span>
                        <span class="mt-2 block text-[10px] uppercase tracking-[0.12em] text-[var(--text-muted)]">{{ $font->preview_label ?: $font->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{--
        @if (($mockups instanceof \Illuminate\Support\Collection ? $mockups : collect($mockups ?? []))->isNotEmpty())
            <div class="surface-card p-5">
                <div class="mb-3 flex items-center gap-3">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[rgba(120,0,0,0.08)] text-sm font-semibold text-[var(--accent-primary)]">3</span>
                    <div>
                        <h2 class="text-base font-semibold text-[var(--text-main)]">Preview in your space</h2>
                        <p class="text-xs text-[var(--text-muted)]">Select a scene to see your certificate displayed</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    @foreach (($mockups instanceof \Illuminate\Support\Collection ? $mockups : collect($mockups ?? [])) as $index => $mockup)
                        <button
                            type="button"
                            class="group text-left"
                            @click="selectMockup({{ $index }})"
                            aria-label="Preview in {{ $mockup['name'] ?? 'Scene preview' }}"
                        >
                            <div class="relative overflow-hidden rounded-lg border-2 transition-all duration-200 ease-out" :class="activeMockup === {{ $index }} && mode === 'mockup' ? 'border-[var(--accent-primary)]' : 'border-transparent group-hover:border-[var(--accent-primary)]'">
                                <img src="{{ $mockup['thumbnail_url'] ?? $mockup['image_url'] }}" alt="{{ $mockup['name'] ?? 'Scene preview' }}" class="aspect-[3/4] w-full object-cover">
                                <span x-cloak x-show="activeMockup === {{ $index }} && mode === 'mockup'" class="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-[var(--accent-primary)] text-[10px] text-white">✓</span>
                            </div>
                            <span class="mt-1 block truncate text-center text-[10px] text-[var(--text-muted)]">{{ $mockup['name'] ?? 'Scene preview' }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
        --}}

        <div class="surface-card p-5" x-data="{ open: false }">
            <button type="button" class="flex w-full items-center justify-between gap-3 text-left text-sm text-[var(--accent-primary)]" @click="open = !open">
                <span>{{ $proofNoteLabel }} +</span>
                <span class="text-lg transition-transform duration-200" :class="open ? 'rotate-45' : ''">+</span>
            </button>

            <div x-cloak x-show="open" x-transition.duration.200ms class="mt-3">
                <textarea
                    name="proof_note_visible"
                    x-model="proofNote"
                    rows="4"
                    placeholder="Mention any spelling, hierarchy, or formatting preferences..."
                    class="field-textarea !rounded-[var(--radius-md)] !bg-[var(--bg-section-soft)] !px-3 !py-2.5 !text-sm"
                ></textarea>
                <p class="mt-1 text-[10px] italic text-[var(--text-muted)]">Our designer reviews every proof before production</p>
            </div>
        </div>

        <div class="surface-card-featured p-5" x-ref="ctaAnchor">
            <button
                type="submit"
                class="button-primary relative mt-0 w-full overflow-hidden !rounded-[var(--radius-xl)] !py-4 !text-base"
            >
                <span x-show="!submitting">Add personalized order</span>
                <span x-cloak x-show="submitting" class="absolute inset-0 flex items-center justify-center bg-[var(--accent-primary)]">
                    <svg class="h-5 w-5 animate-spin text-white" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle>
                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" class="opacity-90"></path>
                    </svg>
                </span>
            </button>

            <button
                type="submit"
                name="buy_now"
                value="1"
                class="button-ghost mt-2 w-full !rounded-[var(--radius-xl)] !py-3.5 !text-sm !text-[var(--accent-primary)]"
            >
                Buy it now
            </button>

            <div class="mt-4 border-t border-[var(--border-soft)] pt-4">
                <div class="grid gap-2 text-[11px] text-[var(--text-muted)] sm:grid-cols-3">
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-[var(--accent-soft)]" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M6.4 11.2 3.2 8l1.1-1.1 2.1 2.1 5-5L12.5 5z"/></svg>
                        <span>Proof before production</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-[var(--accent-soft)]" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 1a3 3 0 0 0-3 3v2H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1h-1V4a3 3 0 0 0-3-3Zm-1.5 5V4a1.5 1.5 0 0 1 3 0v2h-3Z"/></svg>
                        <span>Secure checkout</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-[var(--accent-soft)]" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2 4.5 8 1l6 3.5V12L8 15l-6-3V4.5Zm2 .7V11l4 2.2 4-2.2V5.2L8 3 4 5.2Z"/></svg>
                        <span>Carefully packaged</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
