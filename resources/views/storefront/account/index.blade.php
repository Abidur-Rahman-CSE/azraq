<x-layouts.narrow title="Account | Azraq Bridal">
    <div class="account-shell">
        <section class="surface-card-featured account-hero">
            <div class="account-hero__topline">
                <span class="eyebrow">Account</span>
                <span class="account-hero__meta">Premium bridal concierge</span>
            </div>
            <h1 class="account-hero__title">Your bridal accessories lounge</h1>
            <p class="account-hero__copy">Keep every order, wishlist piece, and service request in one refined dashboard designed for a smooth Azraq shopping journey.</p>
        </section>

        <section class="account-action-grid">
            <a href="{{ route('orders.index') }}" class="surface-card account-action-card">
                <p class="account-action-card__kicker">Orders</p>
                <h2 class="account-action-card__title">Recent orders</h2>
                <p class="account-action-card__desc">Review the latest bridal accessory purchases from this browser session.</p>
                <p class="account-action-card__cta">Explore <span>→</span></p>
            </a>
            <a href="{{ route('wishlist.index') }}" class="surface-card account-action-card">
                <p class="account-action-card__kicker">Wishlist</p>
                <h2 class="account-action-card__title">Saved pieces</h2>
                <p class="account-action-card__desc">Revisit your shortlisted veils, jewelry accents, and personalized add-ons.</p>
                <p class="account-action-card__cta">Explore <span>→</span></p>
            </a>
            <a href="{{ route('orders.track.form') }}" class="surface-card account-action-card">
                <p class="account-action-card__kicker">Tracking</p>
                <h2 class="account-action-card__title">Track an order</h2>
                <p class="account-action-card__desc">Check payment, preparation, and delivery progress from one place.</p>
                <p class="account-action-card__cta">Explore <span>→</span></p>
            </a>
            <a href="{{ route('bookings.index') }}" class="surface-card account-action-card">
                <p class="account-action-card__kicker">Bookings</p>
                <h2 class="account-action-card__title">Service requests</h2>
                <p class="account-action-card__desc">Manage bridal, mehendi, or non-bridal consultations and follow-ups.</p>
                <p class="account-action-card__cta">Explore <span>→</span></p>
            </a>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <div class="surface-card account-module lg:col-span-2">
                <div class="account-module__header">
                    <h2 class="account-module__title">Recent order activity</h2>
                    <a href="{{ route('orders.index') }}" class="account-module__link">View all</a>
                </div>
                <div class="account-feed">
                    @forelse ($orders as $order)
                        <article class="account-feed-item">
                            <div class="account-feed-item__row">
                                <div>
                                    <p class="account-feed-item__title">{{ $order->order_number }}</p>
                                    <p class="account-feed-item__meta">{{ $order->items_count }} items · {{ ucfirst($order->payment_status) }} payment · {{ ucfirst(str_replace('_', ' ', $order->shipping_status)) }}</p>
                                </div>
                                <p class="account-feed-item__amount">BDT {{ number_format((float) $order->total_amount, 0) }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm leading-7 text-(--color-text-soft)">No recent orders yet. Once you place an order, it will appear here for quick access.</p>
                    @endforelse
                </div>
            </div>

            <div class="surface-card account-module">
                <div class="account-module__header">
                    <h2 class="account-module__title">Saved pieces</h2>
                    <a href="{{ route('wishlist.index') }}" class="account-module__link">View all</a>
                </div>
                <div class="account-feed">
                    @forelse ($wishlist as $product)
                        <article class="account-feed-item">
                            <p class="account-feed-item__title text-(--accent-secondary)">{{ $product->name }}</p>
                            <p class="account-feed-item__meta mt-2">{{ $product->category?->name }}</p>
                        </article>
                    @empty
                        <p class="text-sm leading-7 text-(--color-text-soft)">Your wishlist is empty right now.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="surface-card account-module">
            <div class="account-module__header">
                <h2 class="account-module__title">Recent service requests</h2>
                <a href="{{ route('bookings.index') }}" class="account-module__link">View all</a>
            </div>
            <div class="account-feed">
                @forelse ($bookings as $booking)
                    <article class="account-feed-item">
                        <div class="account-feed-item__row">
                            <div>
                                <p class="account-feed-item__title">{{ $booking->booking_number }}</p>
                                <p class="account-feed-item__meta">{{ $booking->product?->name }}</p>
                            </div>
                            <p class="account-feed-item__meta font-semibold">{{ ucfirst($booking->status) }}</p>
                        </div>
                    </article>
                @empty
                    <p class="text-sm leading-7 text-(--color-text-soft)">No service requests on this browser session yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.narrow>
