<x-layouts.narrow title="Tracked Order | Azraq Bridal">
    <div class="space-y-6">
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
                    <article class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="font-medium text-[var(--color-secondary-900)]">{{ $item->product_name }}</p>
                                <p class="mt-1 text-sm text-[var(--color-text-soft)]">Qty {{ $item->quantity }} · {{ ucfirst(str_replace('_', ' ', $item->personalization_status)) }}</p>
                            </div>
                            <p class="font-medium text-[var(--color-secondary-900)]">BDT {{ number_format((float) $item->subtotal_amount, 0) }}</p>
                        </div>
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
