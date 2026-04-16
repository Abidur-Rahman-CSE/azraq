<x-layouts.narrow title="Orders | Azraq Bridal">
    <div class="space-y-6">
        <section class="surface-card-featured p-8">
            <span class="eyebrow">Orders</span>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">Recent orders on this browser session</h1>
            <p class="mt-4 max-w-3xl text-base leading-8 text-[var(--color-text-soft)]">Until full account pages land, this lightweight view keeps recently placed orders accessible from the storefront.</p>
        </section>

        @forelse ($orders as $order)
            <article class="surface-card p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.16em] text-[var(--color-primary-900)]">{{ $order->order_number }}</p>
                        <h2 class="mt-3 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ $order->customer_name }}</h2>
                        <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">{{ $order->items_count }} items · {{ ucfirst($order->payment_status) }} payment · {{ ucfirst(str_replace('_', ' ', $order->shipping_status)) }}</p>
                    </div>
                    <div class="flex flex-col items-start gap-3 md:items-end">
                        <p class="text-lg font-semibold text-[var(--color-secondary-900)]">BDT {{ number_format((float) $order->total_amount, 0) }}</p>
                        <a href="{{ route('orders.success', $order) }}" class="button-ghost">View details</a>
                    </div>
                </div>
            </article>
        @empty
            <section class="surface-card p-10 text-center">
                <h2 class="text-3xl font-semibold text-[var(--color-secondary-900)]">No recent orders yet</h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-soft)]">Place an order from checkout and it will appear here for quick reference.</p>
                <div class="mt-8">
                    <a href="{{ route('shop.index') }}" class="button-primary">Start shopping</a>
                </div>
            </section>
        @endforelse
    </div>
</x-layouts.narrow>
