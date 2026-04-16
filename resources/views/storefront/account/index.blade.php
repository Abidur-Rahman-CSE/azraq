<x-layouts.narrow title="Account | Azraq Bridal">
    <div class="space-y-6">
        <section class="surface-card-featured p-8">
            <span class="eyebrow">Account</span>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">Your Azraq customer hub</h1>
            <p class="mt-4 max-w-3xl text-base leading-8 text-[var(--color-text-soft)]">This lightweight account area keeps the storefront warm and simple while giving you quick access to orders, bookings, wishlist items, and tracking tools.</p>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <a href="{{ route('orders.index') }}" class="surface-card p-6">
                <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Orders</p>
                <h2 class="mt-3 text-2xl font-semibold text-[var(--color-secondary-900)]">Recent orders</h2>
                <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Review recent orders placed from this browser session.</p>
            </a>
            <a href="{{ route('wishlist.index') }}" class="surface-card p-6">
                <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Wishlist</p>
                <h2 class="mt-3 text-2xl font-semibold text-[var(--color-secondary-900)]">Saved products</h2>
                <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Revisit saved bridal pieces, personalized add-ons, and combos.</p>
            </a>
            <a href="{{ route('orders.track.form') }}" class="surface-card p-6">
                <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Tracking</p>
                <h2 class="mt-3 text-2xl font-semibold text-[var(--color-secondary-900)]">Track an order</h2>
                <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Check payment, fulfillment, and shipping progress anytime.</p>
            </a>
            <a href="{{ route('bookings.index') }}" class="surface-card p-6">
                <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Bookings</p>
                <h2 class="mt-3 text-2xl font-semibold text-[var(--color-secondary-900)]">Recent service requests</h2>
                <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">See recent bridal, mehendi, or non-bridal booking requests.</p>
            </a>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <div class="surface-card p-6 lg:col-span-2">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Recent order activity</h2>
                    <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-[var(--color-primary-900)]">View all</a>
                </div>
                <div class="mt-5 space-y-4">
                    @forelse ($orders as $order)
                        <article class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-[var(--color-primary-900)]">{{ $order->order_number }}</p>
                                    <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">{{ $order->items_count }} items · {{ ucfirst($order->payment_status) }} payment · {{ ucfirst(str_replace('_', ' ', $order->shipping_status)) }}</p>
                                </div>
                                <p class="font-semibold text-[var(--color-secondary-900)]">BDT {{ number_format((float) $order->total_amount, 0) }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm leading-7 text-[var(--color-text-soft)]">No recent orders yet. Once you place an order, it will appear here for quick access.</p>
                    @endforelse
                </div>
            </div>

            <div class="surface-card p-6">
                <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Saved pieces</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($wishlist as $product)
                        <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-4">
                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $product->name }}</p>
                            <p class="mt-2 text-xs text-[var(--color-text-soft)]">{{ $product->category?->name }}</p>
                        </div>
                    @empty
                        <p class="text-sm leading-7 text-[var(--color-text-soft)]">Your wishlist is empty right now.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="surface-card p-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Recent service requests</h2>
                <a href="{{ route('bookings.index') }}" class="text-sm font-semibold text-[var(--color-primary-900)]">View all</a>
            </div>
            <div class="mt-5 space-y-4">
                @forelse ($bookings as $booking)
                    <article class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-primary-900)]">{{ $booking->booking_number }}</p>
                                <p class="mt-2 text-sm text-[var(--color-secondary-900)]">{{ $booking->product?->name }}</p>
                            </div>
                            <p class="text-sm text-[var(--color-text-soft)]">{{ ucfirst($booking->status) }}</p>
                        </div>
                    </article>
                @empty
                    <p class="text-sm leading-7 text-[var(--color-text-soft)]">No service requests on this browser session yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.narrow>
