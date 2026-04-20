<x-layouts.narrow title="Tracked Order | Azraq Bridal">
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-[var(--radius-xl)] border border-[rgba(120,0,0,0.14)] bg-[var(--color-surface-cream)] px-5 py-4 text-sm font-medium text-[var(--color-secondary-900)]">
                {{ session('status') }}
            </div>
        @endif

        <section class="surface-card-featured p-8">
            <span class="eyebrow">Tracking result</span>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">{{ $order->order_number }}</h1>
            <p class="mt-4 text-base leading-8 text-[var(--color-text-soft)]">A clean view of payment, fulfillment, and shipping progress for your order.</p>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Payment</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ ucfirst($order->payment_status) }}</p>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Fulfillment</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ ucfirst($order->fulfillment_status) }}</p>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Shipping</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ ucfirst(str_replace('_', ' ', $order->shipping_status)) }}</p>
                </div>
            </div>
        </section>

        <section class="surface-card p-8">
            <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Items in this order</h2>
            <div class="mt-6 space-y-4">
                @foreach ($order->items as $item)
                    @php($proofs = collect(data_get($item->line_item_meta, 'generated_proofs', [])))
                    <article class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="font-medium text-[var(--color-secondary-900)]">{{ $item->product_name }}</p>
                                <p class="mt-1 text-sm text-[var(--color-text-soft)]">Qty {{ $item->quantity }} · {{ ucfirst(str_replace('_', ' ', $item->personalization_status)) }}</p>
                            </div>
                            <p class="font-medium text-[var(--color-secondary-900)]">BDT {{ number_format((float) $item->subtotal_amount, 0) }}</p>
                        </div>

                        @if ($proofs->isNotEmpty())
                            <div class="mt-5 grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
                                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)] p-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Proof files</p>
                                    <div class="mt-4 space-y-3">
                                        @foreach (['flat' => 'Flat proof', 'mockup' => 'Mockup proof'] as $mode => $label)
                                            @php($modeProofs = collect($proofs->get($mode, [])))
                                            @if ($modeProofs->isNotEmpty())
                                                <div>
                                                    <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $label }}</p>
                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        @foreach ($modeProofs as $format => $proof)
                                                            <a href="{{ data_get($proof, 'latest.url') }}" target="_blank" rel="noopener" class="button-ghost !px-3 !py-2">{{ strtoupper($format) }}</a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white p-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Proof decision</p>
                                    <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Review the latest proof files, then confirm approval or request changes.</p>

                                    @if (data_get($item->line_item_meta, 'customer_proof_decision'))
                                        <div class="mt-4 rounded-[var(--radius-lg)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)] px-4 py-4 text-sm">
                                            <p class="font-semibold text-[var(--color-secondary-900)]">Latest response: {{ str(data_get($item->line_item_meta, 'customer_proof_decision'))->headline() }}</p>
                                            @if (data_get($item->line_item_meta, 'customer_proof_note'))
                                                <p class="mt-2 text-[var(--color-text-soft)]">{{ data_get($item->line_item_meta, 'customer_proof_note') }}</p>
                                            @endif
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('orders.proof.update', [$order, $item]) }}" class="mt-4 space-y-3">
                                        @csrf
                                        <input type="hidden" name="customer_email" value="{{ $customerEmail }}">
                                        <label class="field-shell">
                                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Note for the team</span>
                                            <textarea name="note" rows="3" class="field-textarea" placeholder="Optional note about corrections or approval.">{{ old('note') }}</textarea>
                                        </label>
                                        <div class="flex flex-wrap gap-3">
                                            <button type="submit" name="decision" value="approve" class="button-primary">Approve proof</button>
                                            <button type="submit" name="decision" value="changes_requested" class="button-ghost">Request changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('orders.track.form') }}" class="button-ghost">Track another order</a>
                <a href="{{ route('orders.index') }}" class="button-primary">View recent orders</a>
            </div>
        </section>
    </div>
</x-layouts.narrow>
