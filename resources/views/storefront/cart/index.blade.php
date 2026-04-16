<x-layouts.checkout title="Cart | Azraq Bridal">
    <div class="space-y-6">
        <section class="surface-card-featured p-8">
            <span class="eyebrow">Cart</span>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">Your bridal bag</h1>
            <p class="mt-4 max-w-3xl text-base leading-8 text-[var(--color-text-soft)]">Review standard products, personalized Nikah items, combos, and service-led selections in one clean, grouped flow before checkout.</p>
        </section>

        @forelse ($items as $item)
            @php($primaryImage = $item['product']->images->firstWhere('is_primary', true) ?: $item['product']->images->first())
            <article class="surface-card p-6">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
                    <div class="h-36 overflow-hidden rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] lg:w-36">
                        @if ($primaryImage)
                            <img src="{{ $primaryImage->image_url }}" alt="{{ $item['product']->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                        @endif
                    </div>

                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="eyebrow">{{ $item['product']->type?->label() }}</span>
                            @if ($item['product']->category)
                                <x-storefront.trust-badge :label="$item['product']->category->name" />
                            @endif
                        </div>

                        <h2 class="mt-4 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ $item['product']->name }}</h2>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div class="space-y-3 text-sm leading-7 text-[var(--color-text-soft)]">
                                @if ($item['variant'])
                                    <p><span class="font-semibold text-[var(--color-secondary-900)]">Variant:</span> {{ $item['variant']->name }}</p>
                                @endif
                                @if ($item['custom_text'])
                                    <p><span class="font-semibold text-[var(--color-secondary-900)]">Custom text:</span> {{ $item['custom_text'] }}</p>
                                @endif
                                @if ($item['font'])
                                    <p><span class="font-semibold text-[var(--color-secondary-900)]">Font:</span> {{ $item['font']->name }}</p>
                                @endif
                                @if ($item['proof_note'])
                                    <p><span class="font-semibold text-[var(--color-secondary-900)]">Proof note:</span> {{ $item['proof_note'] }}</p>
                                @endif
                            </div>

                            @if (! empty($item['personalization']))
                                <div class="rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] p-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Personalization summary</p>
                                    <div class="mt-3 space-y-2 text-sm leading-7 text-[var(--color-secondary-900)]">
                                        @foreach ($item['personalization'] as $label => $value)
                                            <p>{{ str($label)->headline() }}: {{ $value }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif ($item['product']->type?->value === 'bundle' && $item['product']->bundleItems->isNotEmpty())
                                <div class="rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] p-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Included in this combo</p>
                                    <div class="mt-3 space-y-2 text-sm leading-7 text-[var(--color-secondary-900)]">
                                        @foreach ($item['product']->bundleItems as $bundleItem)
                                            <p>{{ $bundleItem->childProduct?->name }} x {{ $bundleItem->quantity }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="w-full rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5 lg:max-w-xs">
                        <form method="POST" action="{{ route('cart.update', $item['key']) }}" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            <x-storefront.quantity-selector :value="$item['quantity']" />
                            <button type="submit" class="button-ghost w-full">Update quantity</button>
                        </form>

                        <div class="mt-5 space-y-2 text-sm text-[var(--color-text-soft)]">
                            <div class="flex justify-between gap-4">
                                <span>Unit price</span>
                                <span>BDT {{ number_format($item['unit_price'], 0) }}</span>
                            </div>
                            <div class="flex justify-between gap-4 text-base font-semibold text-[var(--color-secondary-900)]">
                                <span>Subtotal</span>
                                <span>BDT {{ number_format($item['subtotal'], 0) }}</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('cart.destroy', $item['key']) }}" class="mt-5">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="button-ghost w-full">Remove item</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <section class="surface-card p-10 text-center">
                <h2 class="text-3xl font-semibold text-[var(--color-secondary-900)]">Your cart is empty</h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-soft)]">Start with curated bridal pieces, personalized Nikah essentials, or a ready-made combo to begin building your order.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('shop.index') }}" class="button-primary">Continue shopping</a>
                    <a href="{{ route('collections.show', 'signature-nikah') }}" class="button-ghost">Explore Signature Nikah</a>
                </div>
            </section>
        @endforelse

        @if ($items->isNotEmpty())
            <section class="surface-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Coupon and delivery note</h2>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Apply an active code here. Shipping is finalized at checkout based on the selected method.</p>
                    </div>
                </div>

                @if ($coupon)
                    <div class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] p-4">
                        <p class="text-sm text-[var(--color-secondary-900)]">Applied coupon: <span class="font-semibold">{{ $coupon->code }}</span></p>
                        <form method="POST" action="{{ route('cart.coupon.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="button-ghost">Remove coupon</button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('cart.coupon.store') }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
                        @csrf
                        <input type="text" name="code" placeholder="Enter coupon code" class="field-input">
                        <button type="submit" class="button-ghost">Apply coupon</button>
                    </form>
                @endif
            </section>
        @endif
    </div>

    <aside class="space-y-6">
        <div class="surface-sidebar p-8">
            <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Order summary</h2>
            <div class="mt-6 space-y-4 text-sm text-[var(--color-text-soft)]">
                <div class="flex justify-between gap-4">
                    <span>Subtotal</span>
                    <span>BDT {{ number_format($summary['subtotal'], 0) }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span>Estimated shipping</span>
                    <span>BDT {{ number_format($summary['shipping'], 0) }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span>Discount</span>
                    <span>-BDT {{ number_format($summary['discount'], 0) }}</span>
                </div>
                <div class="flex justify-between gap-4 border-t border-[var(--color-border-soft)] pt-4 text-base font-semibold text-[var(--color-secondary-900)]">
                    <span>Total</span>
                    <span>BDT {{ number_format($summary['total'], 0) }}</span>
                </div>
            </div>

            <div class="mt-8 rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] p-5 text-sm leading-7 text-[var(--color-secondary-900)]">
                Personalized Nikah items carry their structured summary forward into checkout and order fulfillment automatically.
            </div>

            <div class="mt-8 space-y-3">
                <a href="{{ route('checkout.show') }}" class="button-primary w-full">Proceed to checkout</a>
                <a href="{{ route('shop.index') }}" class="button-ghost w-full">Keep browsing</a>
            </div>
        </div>
    </aside>
</x-layouts.checkout>
