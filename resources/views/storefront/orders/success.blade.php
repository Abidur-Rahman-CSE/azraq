<x-layouts.narrow title="Order Success | Azraq Bridal">
    <div class="space-y-6">
        <section class="surface-card-featured p-8">
            <span class="eyebrow">Order placed</span>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">Thank you, your order is confirmed</h1>
            <p class="mt-4 text-base leading-8 text-[var(--color-text-soft)]">Order number: <span class="font-semibold text-[var(--color-secondary-900)]">{{ $order->order_number }}</span></p>
            <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Payment, shipping, fulfillment, and personalization statuses move independently so your order can be handled clearly from proof to dispatch.</p>
        </section>

        <section class="surface-card p-8">
            <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Order summary</h2>
            <div class="mt-6 space-y-4">
                @foreach ($order->items as $item)
                    <article class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="font-medium text-[var(--color-secondary-900)]">{{ $item->product_name }}</p>
                                <p class="mt-1 text-sm text-[var(--color-text-soft)]">Qty {{ $item->quantity }} · {{ str($item->product_type)->headline() }}</p>
                            </div>
                            <p class="font-medium text-[var(--color-secondary-900)]">BDT {{ number_format((float) $item->subtotal_amount, 0) }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('orders.index') }}" class="button-primary">View recent orders</a>
                <a href="{{ route('orders.track.form') }}" class="button-ghost">Track an order</a>
            </div>
        </section>
    </div>
</x-layouts.narrow>
