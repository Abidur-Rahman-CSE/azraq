<x-layouts.admin
    :title="'Personalization Review '.$order->order_number"
    page-title="Order personalization review"
    page-subtitle="Nikah proof workflow"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Orders', 'href' => route('admin.orders.index')],
        ['label' => $order->order_number, 'href' => route('admin.orders.show', $order)],
        ['label' => 'Personalization review'],
    ]"
>
    @php
        $personalization = collect($meta['personalization'] ?? []);
        $flatPreview = data_get($renderPreview, 'flat.image_url') ?: data_get($renderPreview, 'template.preview_image_url');
        $mockupMap = data_get($renderPreview, 'mockup.map');
        $flatTextLayers = collect(data_get($renderPreview, 'flat.text_layers', []));
        $generatedProofs = collect($meta['generated_proofs'] ?? []);
    @endphp

    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Nikah proof review"
            :title="'Review '.$item->product_name.' for '.$order->customer_name"
            description="Customer inputs, selected font, proof notes, and mapped mockup preview are surfaced together so review decisions stay operational and calm."
        >
            <x-slot:actions>
                <a href="{{ $customerProofUrl }}" class="button-ghost" target="_blank" rel="noopener">Open customer proof link</a>
                <a href="{{ route('admin.orders.personalization.export', [$order, $item, 'flat']) }}" class="button-ghost" target="_blank" rel="noopener">Open flat SVG</a>
                <a href="{{ route('admin.orders.personalization.export', [$order, $item, 'mockup']) }}" class="button-ghost" target="_blank" rel="noopener">Open mockup SVG</a>
                <a href="{{ route('admin.orders.personalization.export', [$order, $item, 'flat', 'png']) }}" class="button-ghost" target="_blank" rel="noopener">Open flat PNG</a>
                <a href="{{ route('admin.orders.personalization.export', [$order, $item, 'mockup', 'png']) }}" class="button-ghost" target="_blank" rel="noopener">Open mockup PNG</a>
                <a href="{{ route('admin.orders.show', $order) }}" class="button-ghost">Back to order</a>
            </x-slot:actions>
        </x-admin.page-header>

        <section class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
            <div class="space-y-6">
                <div class="surface-card p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Order summary</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ $order->order_number }}</h3>
                    <div class="mt-5 grid gap-4 md:grid-cols-2 text-sm text-[var(--color-text-soft)]">
                        <div>
                            <p class="font-medium text-[var(--color-secondary-900)]">Customer</p>
                            <p class="mt-2">{{ $order->customer_name }}</p>
                            <p>{{ $order->customer_email }}</p>
                            <p>{{ $order->customer_phone }}</p>
                        </div>
                        <div>
                            <p class="font-medium text-[var(--color-secondary-900)]">Product</p>
                            <p class="mt-2">{{ $item->product_name }}</p>
                            <p>Qty: {{ $item->quantity }}</p>
                            <p>Status: {{ str($item->personalization_status)->headline() }}</p>
                        </div>
                    </div>
                </div>

                <div class="surface-card p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Customer personalization inputs</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Input payload</h3>
                    <div class="mt-6 space-y-3">
                        @forelse ($personalization as $key => $value)
                            <div class="surface-card-soft flex items-start justify-between gap-4 p-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">{{ str($key)->replace('_', ' ')->headline() }}</p>
                                    <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">{{ $value }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="surface-card-soft p-4 text-sm text-[var(--color-text-soft)]">No structured personalization fields were captured for this line item.</div>
                        @endforelse
                    </div>
                </div>

                <div class="surface-card p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Proof context</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Font and notes</h3>
                    <div class="mt-6 grid gap-4">
                        <div class="surface-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Selected font</p>
                            <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]">{{ $meta['font'] ?? 'No font selected' }}</p>
                        </div>
                        <div class="surface-card-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Proof notes</p>
                            <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">{{ $meta['proof_note'] ?: 'No proof note was supplied by the customer.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="surface-card p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Customer proof access</p>
                            <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Reissue signed customer proof link</h3>
                            <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Share a fresh review link when the older one expires or when a new proof version is ready for approval.</p>
                        </div>

                        <form method="GET" action="{{ route('admin.orders.personalization.show', [$order, $item]) }}" class="flex flex-wrap gap-2">
                            @foreach ([3, 7, 14, 30] as $daysOption)
                                <button
                                    type="submit"
                                    name="proof_link_days"
                                    value="{{ $daysOption }}"
                                    class="{{ $proofLinkDays === $daysOption ? 'button-primary' : 'button-ghost' }}"
                                >
                                    {{ $daysOption }} day{{ $daysOption === 1 ? '' : 's' }}
                                </button>
                            @endforeach
                        </form>
                    </div>

                    <div class="mt-5 surface-card-soft p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Valid for {{ $proofLinkDays }} day{{ $proofLinkDays === 1 ? '' : 's' }}</p>
                                <p class="mt-1 text-xs text-[var(--color-text-soft)]">Expires {{ $customerProofExpiresAt->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</p>
                            </div>
                            <a href="{{ $customerProofUrl }}" class="button-ghost" target="_blank" rel="noopener">Open latest link</a>
                        </div>
                        <label class="mt-4 block">
                            <span class="sr-only">Signed customer proof link</span>
                            <input type="text" readonly value="{{ $customerProofUrl }}" class="field-input font-mono text-xs">
                        </label>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="surface-card p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Generated flat certificate preview</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Template preview</h3>
                    <div class="mt-6 rounded-[var(--radius-2xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)] p-5">
                        <div class="relative mx-auto aspect-[9/13] max-w-[520px] overflow-hidden rounded-[var(--radius-2xl)] border border-[var(--color-border-soft)] bg-white shadow-[var(--shadow-soft)]">
                            @if ($flatPreview)
                                <img src="{{ $flatPreview }}" alt="{{ $currentTemplate?->name ?: $item->product_name }}" class="absolute inset-0 h-full w-full object-cover">
                            @endif
                            @foreach ($flatTextLayers as $layer)
                                <div
                                    class="absolute px-2 text-center"
                                    style="
                                        left: {{ data_get($layer, 'x', 50) }}%;
                                        top: {{ data_get($layer, 'y', 50) }}%;
                                        width: {{ data_get($layer, 'width', 50) }}%;
                                        min-height: {{ data_get($layer, 'height', 8) }}%;
                                        transform: translate(-50%, -50%) rotate({{ data_get($layer, 'rotation', 0) }}deg);
                                        text-align: {{ data_get($layer, 'align') === 'start' ? 'left' : (data_get($layer, 'align') === 'end' ? 'right' : 'center') }};
                                        color: {{ data_get($layer, 'color', '#780000') }};
                                        line-height: {{ data_get($layer, 'line_height', 1.2) }};
                                        letter-spacing: {{ data_get($layer, 'letter_spacing', 0) }}px;
                                        font-size: clamp({{ data_get($layer, 'font_size_min', 14) }}px, 1.4vw, {{ data_get($layer, 'font_size_max', 30) }}px);
                                        z-index: {{ data_get($layer, 'z_index', 1) }};
                                        font-family: {{ data_get($renderPreview, 'font.css_font_family', '"Poppins", sans-serif') }};
                                    "
                                >{{ data_get($layer, 'value') }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="surface-card p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Generated mockup preview</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Lifestyle composition</h3>
                    <div class="mt-6 rounded-[var(--radius-2xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)] p-5">
                        <div class="relative mx-auto aspect-[4/3] max-w-3xl overflow-hidden rounded-[var(--radius-2xl)] border border-[var(--color-border-soft)] bg-white shadow-[var(--shadow-soft)]">
                            @if (data_get($renderPreview, 'mockup.base_image_url'))
                                <img src="{{ data_get($renderPreview, 'mockup.base_image_url') }}" alt="{{ data_get($renderPreview, 'mockup.title') }}" class="absolute inset-0 h-full w-full object-cover">
                            @endif

                            @if ($mockupMap)
                                @php
                                    $xValues = [data_get($mockupMap, 'top_left_x'), data_get($mockupMap, 'top_right_x'), data_get($mockupMap, 'bottom_right_x'), data_get($mockupMap, 'bottom_left_x')];
                                    $yValues = [data_get($mockupMap, 'top_left_y'), data_get($mockupMap, 'top_right_y'), data_get($mockupMap, 'bottom_right_y'), data_get($mockupMap, 'bottom_left_y')];
                                    $minX = min($xValues);
                                    $maxX = max($xValues);
                                    $minY = min($yValues);
                                    $maxY = max($yValues);
                                    $polygon = collect([
                                        [data_get($mockupMap, 'top_left_x'), data_get($mockupMap, 'top_left_y')],
                                        [data_get($mockupMap, 'top_right_x'), data_get($mockupMap, 'top_right_y')],
                                        [data_get($mockupMap, 'bottom_right_x'), data_get($mockupMap, 'bottom_right_y')],
                                        [data_get($mockupMap, 'bottom_left_x'), data_get($mockupMap, 'bottom_left_y')],
                                    ])->map(fn ($point) => ($point[0] * 100).'%' .' '.($point[1] * 100).'%')->implode(', ');
                                @endphp
                                <div
                                    class="absolute overflow-hidden rounded-[22px]"
                                    style="left: {{ $minX * 100 }}%; top: {{ $minY * 100 }}%; width: {{ max(12, ($maxX - $minX) * 100) }}%; height: {{ max(12, ($maxY - $minY) * 100) }}%; clip-path: polygon({{ $polygon }}); opacity: {{ (float) (data_get($mockupMap, 'opacity', 0.95)) }}; box-shadow: 0 20px 40px rgba(0,48,73,{{ max(0.08, (float) data_get($mockupMap, 'shadow_strength', 0.18) * 0.45) }});"
                                >
                                    <div class="relative h-full w-full overflow-hidden rounded-[18px] border border-[rgba(120,0,0,0.12)] bg-white/92">
                                        @if ($flatPreview)
                                            <img src="{{ $flatPreview }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                                        @endif
                                        @foreach ($flatTextLayers as $layer)
                                            <div
                                                class="absolute px-2 text-center"
                                                style="
                                                    left: {{ data_get($layer, 'x', 50) }}%;
                                                    top: {{ data_get($layer, 'y', 50) }}%;
                                                    width: {{ data_get($layer, 'width', 50) }}%;
                                                    min-height: {{ data_get($layer, 'height', 8) }}%;
                                                    transform: translate(-50%, -50%) rotate({{ data_get($layer, 'rotation', 0) }}deg);
                                                    text-align: {{ data_get($layer, 'align') === 'start' ? 'left' : (data_get($layer, 'align') === 'end' ? 'right' : 'center') }};
                                                    color: {{ data_get($layer, 'color', '#780000') }};
                                                    line-height: {{ data_get($layer, 'line_height', 1.2) }};
                                                    letter-spacing: {{ data_get($layer, 'letter_spacing', 0) }}px;
                                                    font-size: clamp(8px, 1vw, {{ data_get($layer, 'font_size_max', 24) }}px);
                                                    z-index: {{ data_get($layer, 'z_index', 1) }};
                                                    font-family: {{ data_get($renderPreview, 'font.css_font_family', '"Poppins", sans-serif') }};
                                                "
                                            >{{ data_get($layer, 'value') }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (data_get($renderPreview, 'mockup.overlay_image_url'))
                                <img src="{{ data_get($renderPreview, 'mockup.overlay_image_url') }}" alt="" class="absolute inset-0 h-full w-full object-cover" style="opacity: {{ max(0.12, (float) data_get($mockupMap, 'highlight_strength', 0.12)) }}">
                            @endif

                            @if (data_get($renderPreview, 'mockup.mask_image_url'))
                                <img src="{{ data_get($renderPreview, 'mockup.mask_image_url') }}" alt="" class="absolute inset-0 h-full w-full object-cover mix-blend-multiply" style="opacity: {{ max(0.12, (float) data_get($mockupMap, 'highlight_strength', 0.12) * 0.9) }}">
                            @endif
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.orders.personalization.update', [$order, $item]) }}" class="surface-card grid gap-5 p-6">
                    @csrf
                    @method('PUT')
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Review controls</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Approve, request change, or regenerate</h3>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Template</span>
                            <select name="template_id" class="field-select">
                                <option value="">Keep current template</option>
                                @foreach ($templates as $templateOption)
                                    <option value="{{ $templateOption->id }}" @selected((string) old('template_id', $currentTemplate?->id) === (string) $templateOption->id)>{{ $templateOption->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Mockup</span>
                            <select name="mockup_id" class="field-select">
                                <option value="">No mockup selected</option>
                                @foreach ($mockups as $mockupOption)
                                    <option value="{{ $mockupOption->id }}" @selected((string) old('mockup_id', $currentMockup?->id) === (string) $mockupOption->id)>{{ $mockupOption->template_label }} • {{ $mockupOption->title }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Internal note</span>
                        <textarea name="internal_note" rows="4" class="field-textarea">{{ old('internal_note', $meta['internal_note'] ?? '') }}</textarea>
                    </label>

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Review note</span>
                        <textarea name="review_note" rows="3" class="field-textarea">{{ old('review_note', $meta['review_note'] ?? '') }}</textarea>
                    </label>

                    <input type="hidden" name="personalization_status" value="{{ old('personalization_status', $item->personalization_status) }}" x-ref="statusInput">

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="button-primary" onclick="this.form.querySelector('[name=personalization_status]').value='proof_approved'">Approve proof</button>
                        <button type="submit" class="button-ghost" onclick="this.form.querySelector('[name=personalization_status]').value='changes_requested'">Request change</button>
                        <button type="submit" class="button-ghost" onclick="this.form.querySelector('[name=personalization_status]').value='proof_regenerated'">Regenerate preview</button>
                    </div>
                </form>

                <div class="surface-card p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Generated proof archive</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Stored assets</h3>
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @foreach (['flat' => 'Flat proof', 'mockup' => 'Mockup proof'] as $mode => $label)
                            <div class="surface-card-soft p-4">
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $label }}</p>
                                @php($proofSet = collect($generatedProofs->get($mode, [])))
                                @if ($proofSet->isNotEmpty())
                                    <div class="mt-4 space-y-3 text-sm">
                                        @foreach ($proofSet as $format => $proof)
                                            @php($latest = data_get($proof, 'latest', []))
                                            @php($history = collect(data_get($proof, 'history', []))->sortByDesc('version')->values())
                                            <div class="rounded-[var(--radius-lg)] border border-[var(--color-border-soft)] bg-white/80 px-3 py-3">
                                                <div class="flex items-center justify-between gap-3">
                                                    <div>
                                                        <p class="font-medium text-[var(--color-secondary-900)]">{{ strtoupper($format) }} · v{{ data_get($latest, 'version', 1) }}</p>
                                                        <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ \Illuminate\Support\Carbon::parse(data_get($latest, 'generated_at'))->diffForHumans() }}</p>
                                                    </div>
                                                    <a href="{{ data_get($latest, 'url') }}" target="_blank" rel="noopener" class="button-ghost !px-3 !py-2">Open latest</a>
                                                </div>
                                                @if ($history->count() > 1)
                                                    <div class="mt-3 space-y-2 border-t border-[var(--color-border-soft)] pt-3">
                                                        @foreach ($history as $version)
                                                            <div class="flex items-center justify-between gap-3 text-xs">
                                                                <p class="text-[var(--color-text-soft)]">v{{ data_get($version, 'version') }} · {{ \Illuminate\Support\Carbon::parse(data_get($version, 'generated_at'))->diffForHumans() }}</p>
                                                                <a href="{{ data_get($version, 'url') }}" target="_blank" rel="noopener" class="font-medium text-[var(--color-primary-900)]">Open</a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">No stored {{ $mode }} proof yet. Use the export buttons above to generate one.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.admin>
