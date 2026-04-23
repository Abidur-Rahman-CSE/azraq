@php
    $orderedFields = $template->fields
        ->sortBy(fn ($field) => str($field->field_key)->contains('groom') ? 0 : (str($field->field_key)->contains('bride') ? 1 : 10 + (int) ($field->position ?? 0)))
        ->values();
    $nameFields = $orderedFields->filter(fn ($field) => str($field->field_key)->contains(['groom', 'bride']))->values();
    $detailFields = $orderedFields->reject(fn ($field) => str($field->field_key)->contains(['groom', 'bride']))->values();
@endphp

<section class="space-y-5 text-[#3D3730]">
    <div class="rounded-[1.85rem] border border-[#E8E3DC] bg-[linear-gradient(180deg,#FFFFFF_0%,#FCFAF6_100%)] p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] sm:p-8">
        <div class="flex flex-wrap gap-2">
            @foreach ($badgeItems as $badge)
                <span class="rounded-full border border-[rgba(139,38,53,0.12)] bg-[#FAF8F5] px-3 py-1 text-xs font-semibold text-[#8B2635]">{{ $badge }}</span>
            @endforeach
        </div>

        <p class="mt-5 text-xs font-semibold uppercase tracking-[0.2em] text-[#C4A882]">Ceremonial keepsake</p>
        <h1 class="mt-3 max-w-2xl font-serif text-3xl font-semibold leading-tight text-[#2C2C3E] sm:text-[2.15rem]">{{ $product->name }}</h1>

        <div class="mt-5 flex flex-wrap items-end gap-3">
            <p class="text-3xl font-semibold tracking-tight text-[#8B2635]">BDT {{ number_format((float) $product->price, 0) }}</p>
            @if ($product->compare_at_price)
                <p class="text-sm text-[#8C7F74] line-through">BDT {{ number_format((float) $product->compare_at_price, 0) }}</p>
            @endif
        </div>

        <p class="mt-3 inline-flex rounded-full bg-[rgba(139,38,53,0.07)] px-3 py-1 text-sm font-medium text-[#8B2635]">Custom proof included before printing</p>
        <p class="mt-5 max-w-2xl text-sm leading-7 text-[#8C7F74]">{{ $shortDescription }}</p>
    </div>

    <form method="POST" action="{{ route('cart.store', $product) }}" class="space-y-5" x-ref="mainProductForm">
        @csrf
        <input type="hidden" name="quantity" value="1">
        <input type="hidden" name="font_id" :value="primaryFontId()">
        @if (($mockups instanceof \Illuminate\Support\Collection ? $mockups : collect($mockups ?? []))->isNotEmpty())
            <input type="hidden" name="mockup_id" :value="window.__MOCKUPS__?.[activeMockup]?.id ?? ''">
        @endif

        <div class="rounded-[1.45rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] sm:p-7">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-serif text-2xl font-semibold text-[#2C2C3E]">Personalise your certificate</h2>
                <span class="rounded-full bg-[#FAF8F5] px-3 py-1 text-xs font-semibold text-[#C4A882]">Step 1</span>
            </div>

            <div class="mt-6 space-y-6">
                @foreach ($nameFields as $field)
                    @php
                        $fieldName = 'personalization.'.$field->field_key;
                        $fontFieldError = 'font_selection.'.$field->field_key;
                        $availableFonts = $fonts
                            ->filter(function ($font) use ($field) {
                                $recommendedFor = str($font->recommended_for ?? 'all')->lower()->replace(' ', '');
                                $fieldKey = str($field->field_key)->lower()->replace(' ', '');

                                return $recommendedFor->isEmpty()
                                    || $recommendedFor->contains('all')
                                    || $recommendedFor->contains($fieldKey);
                            })
                            ->take(8)
                            ->values();
                    @endphp

                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <label for="field-{{ $field->field_key }}" class="text-xs font-semibold uppercase tracking-[0.18em] text-[#2C2C3E]">{{ $field->label }}</label>
                            @if ($field->max_length)
                                <span class="text-xs text-[#8C7F74]">
                                    <span x-text="`${(fields['{{ $field->field_key }}'] || '').length}`">0</span>/{{ $field->max_length }}
                                </span>
                            @endif
                        </div>

                        <input
                            id="field-{{ $field->field_key }}"
                            type="text"
                            name="personalization[{{ $field->field_key }}]"
                            value="{{ old($fieldName, $field->default_value ?? $field->preview_sample_value ?? '') }}"
                            maxlength="{{ $field->max_length }}"
                            placeholder="{{ $field->placeholder }}"
                            x-model="fields['{{ $field->field_key }}']"
                            @input.debounce.200ms="renderPreview()"
                            class="w-full rounded-xl border border-[#D9D2CA] bg-[#FFFFFF] px-4 py-3 text-sm text-[#3D3730] outline-none transition duration-200 ease-out placeholder:text-[#8C7F74] focus:border-[#C4A882] focus:ring-2 focus:ring-[#C4A882]/25"
                        >

                        <input type="hidden" name="font_selection[{{ $field->field_key }}]" :value="fieldFonts['{{ $field->field_key }}'] || ''">

                        <div class="pt-1">
                            <p class="text-sm font-medium text-[#2C2C3E]">Choose a font</p>
                            <div class="mt-3 flex flex-wrap gap-3">
                                @foreach ($availableFonts as $font)
                                    <button
                                        type="button"
                                        class="relative flex h-[84px] w-[84px] items-center justify-center rounded-2xl border bg-white text-[#2C2C3E] transition duration-200 ease-out"
                                        :class="fieldFonts['{{ $field->field_key }}'] === '{{ $font->id }}' ? 'border-[#2C2C3E] shadow-[0_10px_18px_rgba(44,44,62,0.12)]' : 'border-[#E8E3DC]'"
                                        @click="setFieldFont('{{ $field->field_key }}', '{{ $font->id }}')"
                                        aria-label="Choose {{ $font->name }} for {{ $field->label }}"
                                    >
                                        <span class="text-[2rem] leading-none" style="font-family: {{ $font->font_family ?: $font->css_font_family }};">Abc</span>
                                        <span class="absolute bottom-0 right-0 flex h-6 w-6 items-center justify-center rounded-tl-xl rounded-br-[15px] bg-[#2C2C3E] text-xs font-semibold text-white" x-show="fieldFonts['{{ $field->field_key }}'] === '{{ $font->id }}'">✓</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @if ($field->help_text || $errors->has($fieldName) || $errors->has($fontFieldError))
                            <div class="flex items-start justify-between gap-3 text-xs">
                                @if ($field->help_text)
                                    <p class="italic text-[#8C7F74]">{{ $field->help_text }}</p>
                                @endif
                                <div class="text-right">
                                    @error($fieldName)
                                        <p class="font-medium text-red-700">{{ $message }}</p>
                                    @enderror
                                    @error($fontFieldError)
                                        <p class="font-medium text-red-700">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        @if ($detailFields->isNotEmpty())
            <div class="rounded-[1.45rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] sm:p-7">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-serif text-2xl font-semibold text-[#2C2C3E]">Additional details</h2>
                    <span class="rounded-full bg-[#FAF8F5] px-3 py-1 text-xs font-semibold text-[#C4A882]">Step 2</span>
                </div>

                <div class="mt-6 space-y-5">
                    @foreach ($detailFields as $field)
                        @php
                            $fieldName = 'personalization.'.$field->field_key;
                            $isDate = str($field->field_key)->contains('date');
                        @endphp
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <label for="field-{{ $field->field_key }}" class="text-xs font-semibold uppercase tracking-[0.18em] text-[#C4A882]">{{ $field->label }}</label>
                                @if ($field->max_length)
                                    <span class="text-xs text-[#8C7F74]">Max {{ $field->max_length }}</span>
                                @endif
                            </div>

                            @if ($isDate)
                                <input
                                    id="field-{{ $field->field_key }}"
                                    type="date"
                                    name="personalization[{{ $field->field_key }}]"
                                    value="{{ old($fieldName) }}"
                                    x-model="fields['{{ $field->field_key }}']"
                                    @input.debounce.200ms="renderPreview()"
                                    class="w-full rounded-xl border border-[#E8E3DC] bg-[#FAF8F5] px-4 py-3 text-sm text-[#3D3730] outline-none transition duration-200 ease-out focus:border-[#C4A882] focus:ring-2 focus:ring-[#C4A882]/30"
                                >
                            @else
                                <input
                                    id="field-{{ $field->field_key }}"
                                    type="text"
                                    name="personalization[{{ $field->field_key }}]"
                                    value="{{ old($fieldName, $field->default_value ?? $field->preview_sample_value ?? '') }}"
                                    maxlength="{{ $field->max_length }}"
                                    placeholder="{{ $field->placeholder }}"
                                    x-model="fields['{{ $field->field_key }}']"
                                    @input.debounce.200ms="renderPreview()"
                                    class="w-full rounded-xl border border-[#E8E3DC] bg-[#FAF8F5] px-4 py-3 text-sm text-[#3D3730] outline-none transition duration-200 ease-out placeholder:text-[#8C7F74] focus:border-[#C4A882] focus:ring-2 focus:ring-[#C4A882]/30"
                                >
                            @endif

                            @if ($field->help_text || $errors->has($fieldName))
                                <div class="flex items-start justify-between gap-3 text-xs">
                                    @if ($field->help_text)
                                        <p class="italic text-[#8C7F74]">{{ $field->help_text }}</p>
                                    @endif
                                    @error($fieldName)
                                        <p class="font-medium text-red-700">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @error('mockup_id')
                    <p class="mt-4 text-sm font-medium text-red-700">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div class="rounded-[1.45rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] sm:p-7" x-data="{ open: false }">
            <button type="button" class="flex w-full items-center justify-between gap-3 text-left" @click="open = !open">
                <span class="font-serif text-xl font-semibold text-[#2C2C3E]">Add special instructions +</span>
                <span class="text-sm text-[#8C7F74]" x-text="open ? 'Hide' : 'Optional'"></span>
            </button>

            <div class="mt-4 space-y-3" x-show="open" x-transition.duration.200ms>
                <textarea
                    name="proof_note"
                    rows="4"
                    placeholder="Mention any spelling, hierarchy, or formatting preferences..."
                    class="w-full rounded-xl border border-[#E8E3DC] bg-[#FAF8F5] px-4 py-3 text-sm text-[#3D3730] outline-none transition duration-200 ease-out placeholder:text-[#8C7F74] focus:border-[#C4A882] focus:ring-2 focus:ring-[#C4A882]/30"
                >{{ old('proof_note') }}</textarea>
                <p class="text-sm text-[#8C7F74]">Our designer reviews all proofs before production</p>
            </div>
        </div>

        <div class="rounded-[1.55rem] border border-[#E8E3DC] bg-[linear-gradient(180deg,#FFFFFF_0%,#FCFAF6_100%)] p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] sm:p-7" x-ref="ctaAnchor">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#C4A882]">Final step</p>
                    <p class="mt-2 font-serif text-xl font-semibold text-[#2C2C3E]">Submit your personalised order</p>
                </div>
                <p class="text-right text-sm leading-6 text-[#8C7F74]">We send a proof before anything goes to print.</p>
            </div>

            <div class="space-y-3">
                <button type="submit" class="w-full rounded-xl bg-[#8B2635] px-5 py-4 text-base font-semibold text-white shadow-[0_12px_24px_rgba(139,38,53,0.18)] transition duration-200 ease-out hover:bg-[#6D1D29]">Add personalized order</button>
                <button type="submit" name="buy_now" value="1" class="w-full rounded-xl border border-[#8B2635] px-5 py-4 text-base font-semibold text-[#8B2635] transition duration-200 ease-out hover:bg-[#FAF8F5]">Buy it now</button>
            </div>

            <div class="mt-5 grid gap-3 text-sm text-[#3D3730] sm:grid-cols-3">
                <div class="rounded-xl bg-[#FAF8F5] px-4 py-3">✓ Proof sent before production</div>
                <div class="rounded-xl bg-[#FAF8F5] px-4 py-3">✓ Secure checkout</div>
                <div class="rounded-xl bg-[#FAF8F5] px-4 py-3">✓ Carefully packaged & posted</div>
            </div>
        </div>
    </form>
</section>
