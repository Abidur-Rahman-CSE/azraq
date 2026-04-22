<x-layouts.narrow title="Proof Review | Azraq Bridal">
    @php
        $proofs = collect(data_get($item->line_item_meta, 'generated_proofs', []));
        $personalization = collect(data_get($item->line_item_meta, 'personalization', []));
        $flatProofPreviewUrl = data_get($proofs, 'flat.png.latest.url');
        $mockupProofPreviewUrl = data_get($proofs, 'mockup.png.latest.url');
    @endphp

    <div class="space-y-6">
        <section class="surface-card-featured p-8">
            <span class="eyebrow">Proof review</span>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">{{ $item->product_name }}</h1>
            <p class="mt-4 text-base leading-8 text-[var(--color-text-soft)]">Review the latest Nikah proof files and confirm whether this version is approved for production.</p>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Order</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $order->order_number }}</p>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Status</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ ucfirst(str_replace('_', ' ', $item->personalization_status)) }}</p>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Selected font</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ data_get($item->line_item_meta, 'font', 'Default') }}</p>
                </div>
            </div>
        </section>

        <section class="surface-card p-8">
            <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Rendered proof previews</h2>
            <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">These previews use the latest stored proof renders, so you can review the same flat and mockup outputs the Azraq team exported.</p>

            <div class="mt-6 grid gap-6 xl:grid-cols-2">
                @foreach ([
                    'flat' => [
                        'label' => 'Flat proof',
                        'url' => $flatProofPreviewUrl,
                        'aspect' => 'aspect-[9/13]',
                        'maxWidth' => 'max-w-[420px]',
                        'object' => 'object-contain',
                    ],
                    'mockup' => [
                        'label' => 'Mockup proof',
                        'url' => $mockupProofPreviewUrl,
                        'aspect' => 'aspect-[4/3]',
                        'maxWidth' => 'max-w-2xl',
                        'object' => 'object-contain',
                    ],
                ] as $mode => $preview)
                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)] p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $preview['label'] }}</p>

                        @if ($preview['url'])
                            <div class="mt-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white p-4">
                                <div class="relative mx-auto {{ $preview['aspect'] }} w-full {{ $preview['maxWidth'] }} overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white shadow-[var(--shadow-soft)]">
                                    <img src="{{ $preview['url'] }}" alt="{{ $preview['label'] }} for {{ $item->product_name }}" class="absolute inset-0 h-full w-full {{ $preview['object'] }}">
                                </div>
                            </div>
                            <p class="mt-3 text-xs leading-6 text-[var(--color-text-soft)]">Showing the latest exported PNG proof for this {{ $mode }} view.</p>
                        @else
                            <div class="mt-4 rounded-[var(--radius-xl)] border border-dashed border-[var(--color-border-soft)] bg-white/80 px-4 py-5 text-sm leading-7 text-[var(--color-text-soft)]">
                                No rendered PNG preview is stored yet for this {{ $mode }} view. You can still open the latest proof files below.
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="surface-card p-8">
            <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Submitted personalization</h2>
            <div class="mt-6 grid gap-3">
                @foreach ($personalization as $key => $value)
                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-primary-900)]">{{ str($key)->replace('_', ' ')->headline() }}</p>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="surface-card p-8">
            <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Latest proof files</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                @foreach (['flat' => 'Flat proof', 'mockup' => 'Mockup proof'] as $mode => $label)
                    @php($modeProofs = collect($proofs->get($mode, [])))
                    @if ($modeProofs->isNotEmpty())
                        <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)] p-5">
                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $label }}</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($modeProofs as $format => $proof)
                                    <a href="{{ data_get($proof, 'latest.url') }}" target="_blank" rel="noopener" class="button-ghost">{{ strtoupper($format) }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>

        <form method="POST" action="{{ request()->fullUrl() }}" class="surface-card space-y-6 p-8">
            @csrf
            <label class="field-shell">
                <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Note for the Azraq team</span>
                <textarea name="note" rows="4" class="field-textarea" placeholder="Optional note about corrections or approval.">{{ old('note', data_get($item->line_item_meta, 'customer_proof_note')) }}</textarea>
            </label>

            @if (data_get($item->line_item_meta, 'customer_proof_decision'))
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-5 py-4 text-sm text-[var(--color-text-soft)]">
                    Latest response: <span class="font-semibold text-[var(--color-secondary-900)]">{{ str(data_get($item->line_item_meta, 'customer_proof_decision'))->headline() }}</span>
                </div>
            @endif

            <div class="flex flex-wrap gap-3">
                <button type="submit" name="decision" value="approve" class="button-primary">Approve proof</button>
                <button type="submit" name="decision" value="changes_requested" class="button-ghost">Request changes</button>
            </div>
        </form>
    </div>
</x-layouts.narrow>
