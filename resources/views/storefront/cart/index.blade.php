<x-layouts.checkout title="Cart | Azraq Bridal">
    <div class="min-w-0 space-y-6">
        <section class="surface-card-featured overflow-hidden p-5 sm:p-7">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="eyebrow">Cart</span>
                    <h1 class="mt-3 font-serif text-3xl font-semibold leading-tight text-[var(--color-secondary-900)] sm:text-4xl">Your bridal bag</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-[var(--color-text-soft)]">Review products, personalized details, combos, and service selections before checkout.</p>
                </div>

                @if ($items->isNotEmpty())
                    <div class="rounded-full border border-[var(--color-border-soft)] bg-white/80 px-4 py-2 text-sm font-semibold text-[var(--color-secondary-900)]">
                        {{ $items->count() }} {{ str('item')->plural($items->count()) }}
                    </div>
                @endif
            </div>
        </section>

        @forelse ($items as $item)
            @php
                $cartImage = $item['product']->storefront_preview_image_url
                    ?: ($item['product']->images->firstWhere('is_primary', true)?->image_url ?? $item['product']->images->first()?->image_url)
                    ?: asset('images/logo/Azraq.svg');
            @endphp

            <article class="surface-card overflow-hidden">
                <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_260px]">
                    <div class="grid gap-4 p-4 sm:grid-cols-[128px_minmax(0,1fr)] sm:p-5">
                        <div class="aspect-[4/5] overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)] sm:aspect-square">
                            <img src="{{ $cartImage }}" alt="{{ $item['product']->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="eyebrow">{{ $item['product']->type?->label() }}</span>
                                @if ($item['product']->category)
                                    <x-storefront.trust-badge :label="$item['product']->category->name" />
                                @endif
                            </div>

                            <h2 class="mt-3 text-xl font-semibold leading-tight text-[var(--color-secondary-900)] sm:text-2xl">{{ $item['product']->name }}</h2>

                            <div class="mt-4 grid gap-3 text-sm leading-7 text-[var(--color-text-soft)] md:grid-cols-2">
                                <div class="space-y-2">
                                    @if ($item['variant'])
                                        <p><span class="font-semibold text-[var(--color-secondary-900)]">Variant:</span> {{ $item['variant']->name }}</p>
                                    @endif
                                    @if ($item['custom_text'])
                                        <p><span class="font-semibold text-[var(--color-secondary-900)]">Custom text:</span> {{ $item['custom_text'] }}</p>
                                    @endif
                                    @if ($item['font'])
                                        <p><span class="font-semibold text-[var(--color-secondary-900)]">Font:</span> {{ $item['font']->name }}</p>
                                    @endif
                                    @if ($item['mockup'])
                                        <p><span class="font-semibold text-[var(--color-secondary-900)]">Mockup scene:</span> {{ $item['mockup']->title }}</p>
                                    @elseif ($item['mockup_title'] ?? null)
                                        <p><span class="font-semibold text-[var(--color-secondary-900)]">Mockup scene:</span> {{ $item['mockup_title'] }}</p>
                                    @endif
                                    @if ($item['proof_note'])
                                        <p><span class="font-semibold text-[var(--color-secondary-900)]">Proof note:</span> {{ $item['proof_note'] }}</p>
                                    @endif
                                </div>

                                @if (! empty($item['personalization']))
                                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)] p-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Personalization</p>
                                        <div class="mt-3 space-y-2 text-sm leading-6 text-[var(--color-secondary-900)]">
                                            @foreach ($item['personalization'] as $label => $value)
                                                <p><span class="font-semibold">{{ str($label)->headline() }}:</span> {{ $value }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif ($item['product']->type?->value === 'bundle' && $item['product']->bundleItems->isNotEmpty())
                                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)] p-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Combo includes</p>
                                        <div class="mt-3 space-y-2 text-sm leading-6 text-[var(--color-secondary-900)]">
                                            @foreach ($item['product']->bundleItems as $bundleItem)
                                                <p>{{ $bundleItem->childProduct?->name }} x {{ $bundleItem->quantity }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-[var(--color-border-soft)] bg-white/60 p-4 sm:p-5 lg:border-l lg:border-t-0">
                        <div class="space-y-4">
                            <div class="space-y-2 text-sm text-[var(--color-text-soft)]">
                                <div class="flex justify-between gap-4">
                                    <span>Unit price</span>
                                    <span class="font-semibold text-[var(--color-secondary-900)]">BDT {{ number_format($item['unit_price'], 0) }}</span>
                                </div>
                                <div class="flex justify-between gap-4 text-base font-semibold text-[var(--color-secondary-900)]">
                                    <span>Subtotal</span>
                                    <span>BDT {{ number_format($item['subtotal'], 0) }}</span>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('cart.update', $item['key']) }}" class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-3">
                                @csrf
                                @method('PATCH')
                                <div class="flex flex-wrap items-end gap-3">
                                    <x-storefront.quantity-selector :value="$item['quantity']" />
                                    <button type="submit" class="button-ghost min-w-32 flex-1">Update</button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('cart.destroy', $item['key']) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button-ghost w-full !border-[rgba(193,18,31,0.22)] !text-[var(--accent-primary)]">Remove item</button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <section class="surface-card p-8 text-center sm:p-10">
                <h2 class="text-3xl font-semibold text-[var(--color-secondary-900)]">Your cart is empty</h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-soft)]">Start with curated bridal pieces, personalized Nikah essentials, or a ready-made combo to begin building your order.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('shop.index') }}" class="button-primary">Continue shopping</a>
                    <a href="{{ route('collections.show', 'signature-nikah') }}" class="button-ghost">Explore Signature Nikah</a>
                </div>
            </section>
        @endforelse

        @if (($comboSuggestions ?? collect())->isNotEmpty())
            <section class="surface-card-featured p-5 sm:p-7">
                <p class="eyebrow">Upgrade and save</p>
                <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Curated combo options</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-7 text-[var(--color-text-soft)]">Some cart items belong in premium bridal sets. Switch to a combo for better value.</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 xl:grid-cols-3">
                    @foreach ($comboSuggestions as $combo)
                        @php($pricing = \App\Support\ComboPricing::summary($combo))
                        <article class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/85 p-4">
                            <div class="flex gap-4">
                                <div class="h-24 w-24 shrink-0 overflow-hidden rounded-[var(--radius-lg)] bg-[var(--color-surface-cream)]">
                                    @if ($combo->storefront_preview_image_url)
                                        <img src="{{ $combo->storefront_preview_image_url }}" alt="{{ $combo->name }}" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-medium uppercase tracking-[0.14em] text-[var(--accent-primary)]">{{ $combo->marketing_label ?: 'Best value' }}</p>
                                    <h3 class="mt-1 line-clamp-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $combo->name }}</h3>
                                    <p class="mt-2 text-xs text-[var(--color-text-soft)]">Regular total: BDT {{ number_format($pricing['regular_total'], 0) }}</p>
                                    @if ($combo->show_combo_savings_badge ?? true)
                                        <p class="text-sm font-semibold text-[var(--accent-primary)]">Save BDT {{ number_format($pricing['savings_amount'], 0) }}</p>
                                    @else
                                        <p class="text-sm font-semibold text-[var(--accent-primary)]">Combo price BDT {{ number_format($pricing['final_total'], 0) }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                                <a href="{{ route('products.show', $combo) }}" class="button-primary">Upgrade to combo</a>
                                <a href="{{ route('cart.index') }}" class="button-ghost">Keep current item</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($items->isNotEmpty())
            <section class="surface-card p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="eyebrow">Coupon</p>
                        <h2 class="mt-2 text-xl font-semibold text-[var(--color-secondary-900)]">Coupon and delivery note</h2>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Apply an active code here. Shipping is finalized at checkout based on the selected method.</p>
                    </div>
                </div>

                @if ($coupon)
                    <div class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)] p-4">
                        <p class="text-sm text-[var(--color-secondary-900)]">Applied coupon: <span class="font-semibold">{{ $coupon->code }}</span></p>
                        <form method="POST" action="{{ route('cart.coupon.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="button-ghost">Remove coupon</button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('cart.coupon.store') }}" class="mt-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
                        @csrf
                        <input type="text" name="code" placeholder="Enter coupon code" class="field-input">
                        <button type="submit" class="button-ghost">Apply coupon</button>
                    </form>
                @endif
            </section>
        @endif
    </div>

    <aside class="space-y-6">
        <div class="surface-sidebar sticky top-24 p-5 sm:p-6">
            <p class="eyebrow">Summary</p>
            <h2 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Order summary</h2>

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
                    <span>Discount{{ $coupon ? ' ('.$coupon->code.')' : '' }}</span>
                    <span>-BDT {{ number_format($summary['discount'], 0) }}</span>
                </div>
                <div class="flex justify-between gap-4 border-t border-[var(--color-border-soft)] pt-4 text-base font-semibold text-[var(--color-secondary-900)]">
                    <span>Total</span>
                    <span>BDT {{ number_format($summary['total'], 0) }}</span>
                </div>
            </div>

            <div class="mt-6 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)] p-4 text-sm leading-7 text-[var(--color-secondary-900)]">
                Personalized Nikah items carry their structured summary into checkout and fulfillment automatically.
            </div>

            <div class="mt-6 space-y-3">
                <a href="{{ route('checkout.show') }}" class="button-primary w-full">Proceed to checkout</a>
                <a href="{{ route('shop.index') }}" class="button-ghost w-full">Keep browsing</a>
            </div>
        </div>
    </aside>
</x-layouts.checkout>
