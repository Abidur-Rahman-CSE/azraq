<x-layouts.narrow title="Proof Review | Azraq Bridal">
    @php
        $proofs = collect(data_get($item->line_item_meta, 'generated_proofs', []));
        $personalization = collect(data_get($item->line_item_meta, 'personalization', []));
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
