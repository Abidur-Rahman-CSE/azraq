<section class="space-y-4 text-[#3D3730]">
    <div class="rounded-[1.5rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] sm:p-7">
        <div class="flex flex-wrap gap-2">
            @foreach ($badgeItems as $badge)
                <span class="rounded-full bg-[#FAF8F5] px-3 py-1 text-xs font-semibold text-[#8B2635]">{{ $badge }}</span>
            @endforeach
        </div>

        <h1 class="mt-4 font-serif text-3xl font-semibold text-[#2C2C3E]">{{ $product->name }}</h1>

        <div class="mt-4 flex flex-wrap items-end gap-3">
            <p class="text-2xl font-semibold text-[#8B2635]">BDT {{ number_format((float) $product->price, 0) }}</p>
            @if ($product->compare_at_price)
                <p class="text-sm text-[#8C7F74] line-through">BDT {{ number_format((float) $product->compare_at_price, 0) }}</p>
            @endif
        </div>

        <p class="mt-2 text-sm text-[#8B2635]">Custom proof included before printing</p>
        <p class="mt-4 text-sm leading-7 text-[#8C7F74]">{{ $shortDescription }}</p>
    </div>

    <form method="POST" action="{{ route('cart.store', $product) }}" class="space-y-4" x-ref="mainProductForm">
        @csrf
        <input type="hidden" name="quantity" value="1">
        <input type="hidden" name="font_id" :value="primaryFontId()">
        @foreach ($template->fields->filter(fn ($field) => str($field->field_key)->contains(['bride', 'groom'])) as $fontField)
            <input type="hidden" name="font_selection[{{ $fontField->field_key }}]" :value="fieldFonts['{{ $fontField->field_key }}'] || ''">
        @endforeach
        @if (($mockups instanceof \Illuminate\Support\Collection ? $mockups : collect($mockups ?? []))->isNotEmpty())
            <input type="hidden" name="mockup_id" :value="window.__MOCKUPS__?.[activeMockup]?.id ?? ''">
        @endif

        <div class="rounded-[1.25rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)]">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-serif text-2xl font-semibold text-[#2C2C3E]">Personalise your certificate</h2>
                <span class="rounded-full bg-[#FAF8F5] px-3 py-1 text-xs font-semibold text-[#C4A882]">Step 1</span>
            </div>

            <div class="mt-5 space-y-5">
                @foreach ($template->fields as $field)
                    @php
                        $fieldName = 'personalization.'.$field->field_key;
                        $isDate = str($field->field_key)->contains('date');
                        $presetValues = collect(data_get($field->settings, 'preset_values', []))->filter(fn ($value) => filled($value))->values();
                    @endphp
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <label for="field-{{ $field->field_key }}" class="text-xs font-semibold uppercase tracking-[0.18em] text-[#C4A882]">{{ $field->label }}</label>
                            @if ($field->max_length)
                                <span class="text-xs text-[#8C7F74]">Max {{ $field->max_length }}</span>
                            @endif
                        </div>

                        @if ($presetValues->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($presetValues as $presetValue)
                                    <button
                                        type="button"
                                        class="rounded-full border border-[#E8E3DC] bg-[#FAF8F5] px-3 py-1.5 text-xs font-semibold text-[#8B2635] transition hover:border-[#8B2635]"
                                        @click.prevent="fields['{{ $field->field_key }}'] = @js($presetValue); renderPreview()"
                                    >
                                        {{ $presetValue }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if ($isDate)
                            <input
                                id="field-{{ $field->field_key }}"
                                type="date"
                                name="personalization[{{ $field->field_key }}]"
                                value="{{ old($fieldName) }}"
                                x-model="fields['{{ $field->field_key }}']"
                                @input.debounce.300ms="renderPreview()"
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
                                @input.debounce.300ms="renderPreview()"
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
        </div>

        <div class="rounded-[1.25rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)]">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-serif text-2xl font-semibold text-[#2C2C3E]">Choose a font</h2>
                <span class="rounded-full bg-[#FAF8F5] px-3 py-1 text-xs font-semibold text-[#C4A882]">Step 2</span>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach ($fonts as $font)
                    @php($fontKey = (string) $font->id)
                    <label class="cursor-pointer">
                        <input type="radio" value="{{ $font->id }}" class="sr-only" x-model="activeFont" @change="applyNameFont('{{ $font->id }}')" @checked(old('font_id', $fonts->firstWhere('is_default', true)?->id) == $font->id)>
                        <span
                            class="block rounded-xl border p-4 transition duration-200 ease-out"
                            :class="activeFont === '{{ $fontKey }}' ? 'border-[#C4A882] bg-[#FAF8F5]' : 'border-[#E8E3DC] bg-white'"
                        >
                            <span class="text-xs uppercase tracking-[0.18em] text-[#8C7F74]">{{ $font->category }}</span>
                            <span class="mt-3 block text-2xl text-[#2C2C3E]" style="font-family: {{ $font->font_family ?: $font->css_font_family }};">
                                {{ $font->preview_sample_text ?: 'بسم الله الرحمن الرحيم' }}
                            </span>
                            <span class="mt-3 block text-sm font-medium text-[#3D3730]">{{ $font->preview_label ?: $font->name }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="rounded-[1.25rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)]">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-serif text-2xl font-semibold text-[#2C2C3E]">Preview in your space</h2>
                <span class="rounded-full bg-[#FAF8F5] px-3 py-1 text-xs font-semibold text-[#C4A882]">Step 3</span>
            </div>

            <div class="mt-5 flex gap-3 overflow-x-auto pb-1">
                @foreach (($mockups instanceof \Illuminate\Support\Collection ? $mockups : collect($mockups ?? [])) as $index => $mockup)
                    <button
                        type="button"
                        class="relative w-32 flex-none rounded-xl border bg-white p-2 text-left transition duration-200 ease-out"
                        :class="mode === 'mockup' && activeMockup === {{ $index }} ? 'border-[#C4A882] bg-[#FAF8F5]' : 'border-[#E8E3DC]'"
                        @click="selectMockup({{ $index }})"
                    >
                        <img src="{{ $mockup['thumbnail_url'] ?? null }}" alt="{{ $mockup['name'] ?? 'Scene preview' }}" class="h-20 w-full rounded-md object-cover">
                        <p class="mt-2 text-sm font-medium text-[#3D3730]">{{ $mockup['name'] ?? 'Scene preview' }}</p>
                        <span class="absolute right-3 top-3 rounded-full bg-[#8B2635] px-2 py-1 text-[10px] font-semibold text-white" x-show="mode === 'mockup' && activeMockup === {{ $index }}">✓</span>
                    </button>
                @endforeach
            </div>

            @error('mockup_id')
                <p class="mt-3 text-sm font-medium text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-[1.25rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)]" x-data="{ open: false }">
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

        <div class="rounded-[1.25rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)]" x-ref="ctaAnchor">
            <div class="space-y-3">
                <button type="submit" class="w-full rounded-xl bg-[#8B2635] px-5 py-4 text-base font-semibold text-white transition duration-200 ease-out hover:bg-[#6D1D29]">Add personalized order</button>
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
