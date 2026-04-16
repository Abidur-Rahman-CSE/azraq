<x-layouts.checkout title="Checkout | Azraq Bridal">
    <div class="space-y-6" x-data="{ billingSame: @js(old('billing_same_as_shipping', true)) }">
        <section class="surface-card-featured p-8">
            <span class="eyebrow">Checkout</span>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">Complete your order</h1>
            <p class="mt-4 max-w-3xl text-base leading-8 text-[var(--color-text-soft)]">A premium, low-friction checkout flow with clean sections for contact, delivery, payment, notes, and review.</p>
        </section>

        <form method="POST" action="{{ route('checkout.store') }}" class="space-y-6">
            @csrf

            <section class="surface-card p-6">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">1. Contact information</p>
                    <h2 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">How can we reach you?</h2>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <label class="field-shell">
                        <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Full name</span>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="field-input">
                    </label>
                    <label class="field-shell">
                        <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Email</span>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="field-input">
                    </label>
                    <label class="field-shell md:col-span-2">
                        <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Phone</span>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="field-input">
                    </label>
                </div>
            </section>

            <section class="surface-card p-6">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">2. Delivery address</p>
                    <h2 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Where should we send it?</h2>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    @foreach ([
                        'line_1' => 'Address line 1',
                        'line_2' => 'Address line 2',
                        'city' => 'City',
                        'area' => 'Area / region',
                        'postal_code' => 'Postal code',
                        'country' => 'Country',
                    ] as $key => $label)
                        <label class="field-shell">
                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $label }}</span>
                            <input type="text" name="shipping_address[{{ $key }}]" value="{{ old('shipping_address.'.$key, $key === 'country' ? 'Bangladesh' : '') }}" class="field-input">
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="surface-card p-6">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">3. Shipping and payment</p>
                    <h2 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Select your fulfillment flow</h2>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <label class="field-shell">
                        <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Shipping method</span>
                        <select name="shipping_method" class="field-select">
                            <option value="standard" @selected(old('shipping_method', 'standard') === 'standard')>Standard delivery</option>
                            <option value="express" @selected(old('shipping_method') === 'express')>Express delivery</option>
                        </select>
                    </label>

                    <div class="space-y-2">
                        <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Payment method</span>
                        <div class="grid gap-3">
                            @foreach (['cod' => 'Cash on delivery', 'online' => 'Online payment placeholder'] as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="{{ $value }}" class="peer sr-only" @checked(old('payment_method', 'cod') === $value)>
                                    <span class="flex rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] px-4 py-4 text-sm font-medium text-[var(--color-secondary-900)] peer-checked:border-[var(--color-primary-900)] peer-checked:bg-[var(--color-surface-cream)]">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="surface-card p-6">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">4. Billing and note</p>
                    <h2 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Anything else we should know?</h2>
                </div>

                <label class="inline-flex items-center gap-3 text-sm text-[var(--color-secondary-900)]">
                    <input type="hidden" name="billing_same_as_shipping" value="0">
                    <input type="checkbox" name="billing_same_as_shipping" value="1" @checked(old('billing_same_as_shipping', true)) x-model="billingSame" class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                    Billing address is the same as shipping
                </label>

                <div class="mt-6 grid gap-5 md:grid-cols-2" x-show="! billingSame" x-cloak>
                    @foreach ([
                        'line_1' => 'Billing address line 1',
                        'line_2' => 'Billing address line 2',
                        'city' => 'Billing city',
                        'area' => 'Billing area / region',
                        'postal_code' => 'Billing postal code',
                        'country' => 'Billing country',
                    ] as $key => $label)
                        <label class="field-shell">
                            <span class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $label }}</span>
                            <input type="text" name="billing_address[{{ $key }}]" value="{{ old('billing_address.'.$key) }}" class="field-input">
                        </label>
                    @endforeach
                </div>

                <label class="field-shell mt-6">
                    <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Order notes</span>
                    <textarea name="notes" rows="4" class="field-textarea">{{ old('notes') }}</textarea>
                </label>
            </section>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="button-primary">Place order</button>
                <a href="{{ route('cart.index') }}" class="button-ghost">Back to cart</a>
            </div>
        </form>
    </div>

    <aside class="space-y-6">
        <div class="surface-sidebar p-8">
            <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Order summary</h2>
            <div class="mt-6 space-y-5">
                @foreach ($items as $item)
                    <div class="border-b border-[var(--color-border-soft)] pb-5 last:border-0 last:pb-0">
                        <div class="flex justify-between gap-4">
                            <div>
                                <p class="font-medium text-[var(--color-secondary-900)]">{{ $item['product']->name }}</p>
                                <p class="mt-1 text-sm text-[var(--color-text-soft)]">Qty {{ $item['quantity'] }}</p>
                                @if (! empty($item['personalization']))
                                    <div class="mt-2 space-y-1 text-xs leading-6 text-[var(--color-text-soft)]">
                                        @foreach ($item['personalization'] as $label => $value)
                                            <p>{{ str($label)->headline() }}: {{ $value }}</p>
                                        @endforeach
                                    </div>
                                @elseif ($item['custom_text'])
                                    <p class="mt-2 text-xs leading-6 text-[var(--color-text-soft)]">Custom text: {{ $item['custom_text'] }}</p>
                                @endif
                            </div>
                            <p class="font-medium text-[var(--color-secondary-900)]">BDT {{ number_format($item['subtotal'], 0) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="surface-card p-8">
            <div class="space-y-4 text-sm text-[var(--color-text-soft)]">
                <div class="flex justify-between gap-4">
                    <span>Subtotal</span>
                    <span>BDT {{ number_format($summary['subtotal'], 0) }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span>Shipping</span>
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

            <div class="mt-6 rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] p-5 text-sm leading-7 text-[var(--color-secondary-900)]">
                Personalized Nikah products show a compact recap here so the proof-critical details stay visible right before order placement.
            </div>
        </div>
    </aside>
</x-layouts.checkout>
