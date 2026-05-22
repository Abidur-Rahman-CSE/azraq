<x-layouts.admin title="Fonts | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Personalization</p>
                <h2 class="mt-1 text-3xl font-semibold text-[var(--color-secondary-900)]">Font library</h2>
                <p class="mt-1 text-sm text-[var(--color-text-soft)]">Global fonts available when setting up personalization templates.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-[rgba(31,143,95,0.18)] bg-[rgba(31,143,95,0.08)] px-5 py-3 text-sm text-[var(--color-success)]">{{ session('status') }}</div>
        @endif

        {{-- ── EXISTING FONTS ─────────────────────────────────────── --}}
        @if ($fonts->isNotEmpty())
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($fonts as $font)
                    <details class="group rounded-[24px] border border-[var(--color-border-soft)] bg-white/90 shadow-[0_8px_24px_rgba(0,48,73,0.05)]">
                        <summary class="cursor-pointer list-none p-5">
                            {{-- Font name in its own typeface --}}
                            <p class="text-lg font-semibold text-[var(--color-secondary-900)]"
                               style="font-family: {{ $font->font_family ?: $font->css_font_family }}; font-weight: {{ $font->font_weight_default ?: '600' }};">
                                {{ $font->name }}
                            </p>
                            {{-- Sample text --}}
                            <p class="mt-2 text-sm text-[var(--color-text-soft)]"
                               style="font-family: {{ $font->font_family ?: $font->css_font_family }};">
                                {{ $font->preview_sample_text ?: 'بسم الله الرحمن الرحيم' }}
                            </p>
                            <p class="mt-1.5 truncate text-[10px] text-[var(--color-text-soft)] opacity-70">{{ $font->css_font_family }}</p>
                            <span class="mt-2 inline-block rounded-full bg-[rgba(253,240,213,0.95)] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--color-primary-900)]">
                                {{ $font->category ?: 'Font' }}
                            </span>
                        </summary>

                        <form method="POST" action="{{ route('admin.personalization.fonts.update', $font) }}" class="border-t border-[var(--color-border-soft)] px-5 pb-5 pt-4 space-y-3">
                            @csrf @method('PUT')
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">Name</span>
                                    <input type="text" name="name" value="{{ $font->name }}" class="w-full rounded-xl border border-[var(--color-border-soft)] px-3 py-2 text-sm">
                                </label>
                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">Font family</span>
                                    <input type="text" name="css_font_family" value="{{ $font->css_font_family }}" class="w-full rounded-xl border border-[var(--color-border-soft)] px-3 py-2 text-sm font-mono">
                                </label>
                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">Internal name</span>
                                    <input type="text" name="internal_name" value="{{ $font->internal_name }}" class="w-full rounded-xl border border-[var(--color-border-soft)] px-3 py-2 text-sm font-mono">
                                </label>
                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">Category</span>
                                    <input type="text" name="category" value="{{ $font->category }}" class="w-full rounded-xl border border-[var(--color-border-soft)] px-3 py-2 text-sm">
                                </label>
                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">Source type</span>
                                    <select name="font_source_type" class="w-full rounded-xl border border-[var(--color-border-soft)] px-3 py-2 text-sm">
                                        <option value="google" @selected($font->font_source_type==='google')>Google Fonts</option>
                                        <option value="local" @selected($font->font_source_type==='local')>Local / system</option>
                                        <option value="uploaded" @selected($font->font_source_type==='uploaded')>Uploaded</option>
                                    </select>
                                </label>
                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">Source value (Google name)</span>
                                    <input type="text" name="font_source_value" value="{{ $font->font_source_value }}" class="w-full rounded-xl border border-[var(--color-border-soft)] px-3 py-2 text-sm">
                                </label>
                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">Preview sample</span>
                                    <input type="text" name="preview_sample_text" value="{{ $font->preview_sample_text }}" class="w-full rounded-xl border border-[var(--color-border-soft)] px-3 py-2 text-sm">
                                </label>
                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">Default weight</span>
                                    <select name="font_weight_default" class="w-full rounded-xl border border-[var(--color-border-soft)] px-3 py-2 text-sm">
                                        @foreach(['400'=>'Regular','500'=>'Medium','600'=>'Semibold','700'=>'Bold','800'=>'Extra bold'] as $val=>$lbl)
                                            <option value="{{ $val }}" @selected($font->font_weight_default==$val)>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked($font->is_active) class="h-4 w-4 rounded">
                                Active
                            </label>
                            <div class="flex items-center gap-2 pt-1">
                                <button type="submit" class="button-primary !py-2 !text-sm">Save</button>
                                <form method="POST" action="{{ route('admin.personalization.fonts.destroy', $font) }}" onsubmit="return confirm('Delete this font?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="button-ghost !py-2 !text-sm text-red-700">Delete</button>
                                </form>
                            </div>
                        </form>
                    </details>
                @endforeach
            </div>
        @else
            <p class="text-sm text-[var(--color-text-soft)]">No global fonts yet. Add one below or import from starters.</p>
        @endif

        {{-- ── ADD NEW FONT ────────────────────────────────────────── --}}
        <section class="surface-card p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Add font</p>
            <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">New font entry</h3>

            <form method="POST" action="{{ route('admin.personalization.fonts.store') }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="space-y-1.5">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Font name *</span>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Classic Script" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    </label>
                    <label class="space-y-1.5">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">CSS font-family *</span>
                        <input type="text" name="css_font_family" value="{{ old('css_font_family') }}" placeholder='"Great Vibes", cursive' class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3 font-mono text-sm">
                    </label>
                    <label class="space-y-1.5">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Internal name *</span>
                        <input type="text" name="internal_name" value="{{ old('internal_name') }}" placeholder="classic_script" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3 font-mono text-sm">
                    </label>
                    <label class="space-y-1.5">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Google Font name</span>
                        <input type="text" name="font_source_value" value="{{ old('font_source_value') }}" placeholder="Great Vibes" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                        <input type="hidden" name="font_source_type" value="google">
                    </label>
                    <label class="space-y-1.5">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Category</span>
                        <input type="text" name="category" value="{{ old('category') }}" placeholder="Classic Script" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    </label>
                    <label class="space-y-1.5">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Default weight</span>
                        <select name="font_weight_default" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                            <option value="400">Regular (400)</option>
                            <option value="500">Medium (500)</option>
                            <option value="600" selected>Semibold (600)</option>
                            <option value="700">Bold (700)</option>
                        </select>
                    </label>
                </div>
                <button type="submit" class="button-primary">Add font</button>
            </form>
        </section>

        {{-- ── STARTER PRESETS ────────────────────────────────────── --}}
        <section class="surface-card p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Quick add</p>
            <h3 class="text-xl font-semibold text-[var(--color-secondary-900)]">Starter presets</h3>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($starterPresets as $preset)
                    <form method="POST" action="{{ route('admin.personalization.fonts.store') }}">
                        @csrf
                        <input type="hidden" name="name" value="{{ $preset['name'] }}">
                        <input type="hidden" name="internal_name" value="{{ $preset['internal_name'] }}">
                        <input type="hidden" name="css_font_family" value="{{ $preset['css_font_family'] }}">
                        <input type="hidden" name="font_family" value="{{ $preset['font_family'] }}">
                        <input type="hidden" name="font_source_type" value="{{ $preset['font_source_type'] }}">
                        <input type="hidden" name="font_source_value" value="{{ $preset['font_source_value'] ?? '' }}">
                        <input type="hidden" name="category" value="{{ $preset['category'] }}">
                        <input type="hidden" name="preview_sample_text" value="{{ $preset['preview_sample_text'] ?? '' }}">
                        <input type="hidden" name="font_weight_default" value="{{ $preset['font_weight_default'] ?? '600' }}">
                        <button type="submit"
                                class="w-full rounded-[20px] border border-[var(--color-border-soft)] bg-[rgba(253,240,213,0.40)] px-4 py-3 text-left transition hover:border-[var(--color-primary-900)] hover:bg-[rgba(253,240,213,0.70)]">
                            <p class="text-sm font-semibold" style="font-family: {{ $preset['font_family'] ?? 'inherit' }}">{{ $preset['name'] }}</p>
                            <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $preset['css_font_family'] }}</p>
                        </button>
                    </form>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.admin>
