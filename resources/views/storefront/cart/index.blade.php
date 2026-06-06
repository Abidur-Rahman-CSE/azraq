<x-layouts.checkout title="Cart | Azraq Bridal">
    <div class="min-w-0 space-y-6">
        <section class="px-1">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="eyebrow">Cart</p>
                    <h1 class="mt-3 text-3xl font-semibold leading-tight text-[var(--color-secondary-900)] sm:text-4xl">Your bridal bag</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-[var(--color-text-soft)]">Review quantities and checkout when everything feels right.</p>
                </div>

                @if ($items->isNotEmpty())
                    <span class="inline-flex w-fit rounded-full bg-white px-4 py-2 text-sm font-semibold text-[var(--color-secondary-900)] shadow-sm">
                        {{ $items->count() }} {{ str('item')->plural($items->count()) }}
                    </span>
                @endif
            </div>
        </section>

        @if ($items->isNotEmpty())
            <section class="overflow-hidden rounded-2xl border border-[var(--color-border-soft)] bg-white shadow-[0_18px_60px_rgba(15,46,60,0.07)]">
                <div class="divide-y divide-[var(--color-border-soft)]">
                    @foreach ($items as $item)
                        @php
                            $cartImage = $item['product']->storefront_preview_image_url
                                ?: ($item['product']->images->firstWhere('is_primary', true)?->image_url ?? $item['product']->images->first()?->image_url)
                                ?: asset('images/logo/Azraq.svg');
                            $quantity = (int) $item['quantity'];
                            $variantOptions = collect($item['variant']?->option_values ?? [])
                                ->filter(fn ($entry) => is_string($entry) && str_contains($entry, ':'))
                                ->map(function (string $entry) {
                                    [$label, $value] = array_pad(explode(':', $entry, 2), 2, '');

                                    return [
                                        'label' => str($label)->trim()->replace('_', ' ')->headline()->toString(),
                                        'value' => trim($value),
                                    ];
                                })
                                ->filter(fn ($option) => filled($option['label']) && filled($option['value']))
                                ->values();

                            if ($variantOptions->isEmpty() && $item['variant']) {
                                $variantOptions = collect([[
                                    'label' => 'Variant',
                                    'value' => $item['variant']->name,
                                ]]);
                            }

                            $customRows = collect($item['personalization'] ?? [])
                                ->filter(fn ($value) => filled($value))
                                ->map(fn ($value, $label) => [
                                    'label' => str($label)->headline()->toString(),
                                    'value' => $value,
                                ])
                                ->values();

                            if (filled($item['custom_text'] ?? null)) {
                                $customRows->push(['label' => 'Custom text', 'value' => $item['custom_text']]);
                            }

                            if ($item['font']) {
                                $customRows->push(['label' => 'Font', 'value' => $item['font']->name]);
                            }

                            foreach (($item['font_selection_fonts'] ?? collect()) as $fieldKey => $font) {
                                if ($font) {
                                    $customRows->push([
                                        'label' => str($fieldKey)->headline()->append(' font')->toString(),
                                        'value' => $font->name,
                                    ]);
                                }
                            }

                            if ($item['mockup']) {
                                $customRows->push(['label' => 'Mockup scene', 'value' => $item['mockup']->title]);
                            } elseif (filled($item['mockup_title'] ?? null)) {
                                $customRows->push(['label' => 'Mockup scene', 'value' => $item['mockup_title']]);
                            }

                            if (filled($item['proof_note'] ?? null)) {
                                $customRows->push(['label' => 'Proof note', 'value' => $item['proof_note']]);
                            }

                            $bundleItems = collect(data_get($item, 'bundle_summary.items', []));
                            $hasExpandableDetails = $customRows->isNotEmpty() || $bundleItems->isNotEmpty();
                            $comboUpsell = ($comboUpsells ?? collect())->get($item['key']);
                        @endphp

                        <article class="grid gap-4 px-4 py-4 sm:px-5 lg:grid-cols-[minmax(0,1fr)_190px] lg:items-start">
                            <div class="grid min-w-0 grid-cols-[88px_minmax(0,1fr)] gap-4 sm:grid-cols-[104px_minmax(0,1fr)]">
                                <a href="{{ route('products.show', $item['product']) }}" class="aspect-square overflow-hidden rounded-xl border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)]">
                                    <img src="{{ $cartImage }}" alt="{{ $item['product']->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                </a>

                                <div class="min-w-0">
                                    <a href="{{ route('products.show', $item['product']) }}" class="block text-lg font-semibold leading-snug text-[var(--color-secondary-900)] transition hover:text-[var(--accent-primary)]">
                                        {{ $item['product']->name }}
                                    </a>

                                    @if ($variantOptions->isNotEmpty())
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            @foreach ($variantOptions as $option)
                                                <span class="inline-flex max-w-full items-center gap-1.5 rounded-full border border-[rgba(120,0,0,0.12)] bg-[rgba(120,0,0,0.05)] px-2.5 py-1 text-xs text-[var(--accent-primary)]">
                                                    <span class="font-semibold">{{ $option['label'] }}:</span>
                                                    <span class="truncate">{{ $option['value'] }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)] lg:hidden">BDT {{ number_format($item['subtotal'], 0) }}</p>

                                    @if ($hasExpandableDetails)
                                        <details class="group mt-3">
                                            <summary class="flex cursor-pointer list-none items-center gap-2 text-sm font-semibold text-[var(--color-text-soft)] transition hover:text-[var(--color-secondary-900)]">
                                                <span class="grid h-6 w-6 place-items-center rounded-full border border-[var(--color-border-soft)] text-base leading-none group-open:hidden">+</span>
                                                <span class="hidden h-6 w-6 place-items-center rounded-full border border-[var(--color-border-soft)] text-base leading-none group-open:grid">-</span>
                                                <span>Item details</span>
                                            </summary>

                                            <div class="mt-3 space-y-3 rounded-xl border border-[rgba(0,48,73,0.06)] bg-[rgba(0,48,73,0.025)] px-3 py-3">
                                                @if ($customRows->isNotEmpty())
                                                    <div class="grid gap-2 text-sm leading-6 text-[var(--color-secondary-900)] sm:grid-cols-2">
                                                        @foreach ($customRows as $row)
                                                            <p class="min-w-0">
                                                                <span class="font-semibold">{{ $row['label'] }}:</span>
                                                                <span class="break-words text-[var(--color-text-soft)]">{{ $row['value'] }}</span>
                                                            </p>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if ($bundleItems->isNotEmpty())
                                                    <div class="{{ $customRows->isNotEmpty() ? 'border-t border-[rgba(0,48,73,0.08)] pt-3' : '' }}">
                                                        <p class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-[var(--color-text-soft)]">Combo includes</p>
                                                        <div class="mt-2 grid gap-2 text-sm leading-6 text-[var(--color-secondary-900)]">
                                                            @foreach ($bundleItems as $bundleItem)
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    <span>{{ $bundleItem['product_name'] ?? $bundleItem['name'] }} x {{ $bundleItem['quantity'] ?? 1 }}</span>
                                                                    @foreach (collect($bundleItem['selected_options'] ?? []) as $group => $value)
                                                                        <span class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-[var(--accent-primary)]">{{ str($group)->replace('_', ' ')->headline() }}: {{ $value }}</span>
                                                                    @endforeach
                                                                    @if (blank($bundleItem['selected_options'] ?? []) && filled($bundleItem['default_variant_name'] ?? null))
                                                                        <span class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-[var(--accent-primary)]">Variant: {{ $bundleItem['default_variant_name'] }}</span>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </details>
                                    @endif

                                    @if ($comboUpsell)
                                        @php
                                            $upsellPricing = $comboUpsell['pricing'];
                                            $upsellCombo = $comboUpsell['combo'];
                                        @endphp
                                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-[rgba(0,48,73,0.10)] bg-[rgba(102,155,188,0.08)] px-3 py-2 text-sm">
                                            <p class="min-w-0 text-[var(--color-secondary-900)]">
                                                <span class="font-semibold">Save BDT {{ number_format($upsellPricing['savings_amount'], 0) }}</span>
                                                @if (($upsellPricing['savings_percent'] ?? 0) > 0)
                                                    <span class="text-[var(--color-text-soft)]">({{ $upsellPricing['savings_percent'] }}%)</span>
                                                @endif
                                                <span class="text-[var(--color-text-soft)]">with {{ $upsellCombo->name }}</span>
                                            </p>
                                            <a href="#curated-combo-options" data-combo-jump class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--accent-secondary)] underline decoration-[rgba(0,48,73,0.25)] underline-offset-4">See combo</a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4 border-t border-[var(--color-border-soft)] pt-4 lg:flex-col lg:items-end lg:border-t-0 lg:pt-0">
                                <div class="hidden text-right lg:block">
                                    <p class="text-xs text-[var(--color-text-soft)]">Subtotal</p>
                                    <p class="mt-1 text-lg font-semibold text-[var(--color-secondary-900)]">BDT {{ number_format($item['subtotal'], 0) }}</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('cart.update', $item['key']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ max(1, $quantity - 1) }}">
                                        <button type="submit" class="grid h-10 w-10 place-items-center rounded-full border border-[var(--color-border-soft)] bg-white text-xl font-semibold text-[var(--color-secondary-900)] transition hover:border-[var(--accent-primary)] disabled:cursor-not-allowed disabled:opacity-35" aria-label="Decrease quantity" @disabled($quantity <= 1)>-</button>
                                    </form>

                                    <span class="grid h-10 min-w-12 place-items-center rounded-full border border-[var(--color-border-soft)] bg-white px-4 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $quantity }}</span>

                                    <form method="POST" action="{{ route('cart.update', $item['key']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ min(20, $quantity + 1) }}">
                                        <button type="submit" class="grid h-10 w-10 place-items-center rounded-full border border-[var(--color-border-soft)] bg-white text-xl font-semibold text-[var(--color-secondary-900)] transition hover:border-[var(--accent-primary)] disabled:cursor-not-allowed disabled:opacity-35" aria-label="Increase quantity" @disabled($quantity >= 20)>+</button>
                                    </form>

                                    <form method="POST" action="{{ route('cart.destroy', $item['key']) }}" class="ml-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="grid h-10 w-10 place-items-center rounded-full border border-[rgba(120,0,0,0.16)] bg-[rgba(120,0,0,0.04)] text-[var(--accent-primary)] transition hover:border-[var(--accent-primary)] hover:bg-[rgba(120,0,0,0.08)]" aria-label="Remove item">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M3 6h18" />
                                                <path d="M8 6V4h8v2" />
                                                <path d="M19 6l-1 14H6L5 6" />
                                                <path d="M10 11v5" />
                                                <path d="M14 11v5" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @else
            <section class="surface-card p-8 text-center sm:p-10">
                <h2 class="text-3xl font-semibold text-[var(--color-secondary-900)]">Your cart is empty</h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-soft)]">Start with curated bridal pieces, personalized Nikah essentials, or a ready-made combo to begin building your order.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('shop.index') }}" class="button-primary">Continue shopping</a>
                    <a href="{{ route('collections.show', 'signature-nikah') }}" class="button-ghost">Explore Signature Nikah</a>
                </div>
            </section>
        @endif

        @if (($comboSuggestions ?? collect())->isNotEmpty())
            <section id="curated-combo-options" class="combo-suggestions rounded-2xl border border-[rgba(0,48,73,0.10)] bg-white p-4 shadow-[0_18px_50px_rgba(15,46,60,0.06)]">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="inline-flex rounded-full bg-[rgba(102,155,188,0.10)] px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-[var(--accent-secondary)]">Upgrade and save</p>
                        <h2 class="mt-2 text-xl font-semibold text-[var(--color-secondary-900)]">Curated combo options</h2>
                        <p class="mt-1 text-sm leading-6 text-[var(--color-text-soft)]">A better-value set matched with items already in your bag.</p>
                    </div>
                    <span class="w-fit rounded-full border border-[rgba(120,0,0,0.10)] bg-[rgba(120,0,0,0.04)] px-3 py-1.5 text-xs font-semibold text-[var(--accent-primary)]">{{ $comboSuggestions->count() }} option{{ $comboSuggestions->count() > 1 ? 's' : '' }}</span>
                </div>

                <div class="mt-4 grid gap-3 xl:grid-cols-3">
                    @foreach ($comboSuggestions as $combo)
                        @php($pricing = \App\Support\ComboPricing::summary($combo))
                        <article class="grid gap-3 rounded-2xl border border-[rgba(0,48,73,0.09)] bg-[rgba(255,255,255,0.92)] p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_16px_36px_rgba(15,46,60,0.10)]">
                            <div class="flex gap-3">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-[rgba(0,48,73,0.04)]">
                                    @if ($combo->storefront_preview_image_url)
                                        <img src="{{ $combo->storefront_preview_image_url }}" alt="{{ $combo->name }}" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="line-clamp-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $combo->name }}</h3>
                                    <p class="mt-1 text-xs text-[var(--color-text-soft)]">Combo price BDT {{ number_format($pricing['final_total'], 0) }}</p>
                                    @if (($combo->show_combo_savings_badge ?? true) && $pricing['savings_amount'] > 0)
                                        <p class="text-xs font-semibold text-[var(--accent-primary)]">Save BDT {{ number_format($pricing['savings_amount'], 0) }}</p>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('products.show', $combo) }}" class="button-primary !py-2.5 !text-sm">View combo</a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <aside class="space-y-5">
        <div class="sticky top-24 rounded-2xl bg-white p-5 shadow-[0_18px_60px_rgba(15,46,60,0.08)]">
            <p class="eyebrow">Summary</p>
            <h2 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Order summary</h2>

            @if ($items->isNotEmpty())
                <div class="mt-5 border-b border-[var(--color-border-soft)] pb-5">
                    @if ($coupon)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-[rgba(0,48,73,0.08)] bg-[rgba(0,48,73,0.025)] px-3 py-3">
                            <p class="text-sm text-[var(--color-secondary-900)]">Coupon <span class="font-semibold">{{ $coupon->code }}</span></p>
                            <form method="POST" action="{{ route('cart.coupon.destroy') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-[var(--accent-primary)]">Remove</button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('cart.coupon.store') }}" class="grid gap-2">
                            @csrf
                            <label class="space-y-2">
                                <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Coupon code</span>
                                <input type="text" name="code" placeholder="Enter code" class="field-input">
                            </label>
                            <button type="submit" class="button-ghost w-full">Apply coupon</button>
                        </form>
                    @endif
                </div>
            @endif

            <div class="mt-5 space-y-4 text-sm text-[var(--color-text-soft)]">
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
                <div class="flex justify-between gap-4 border-t border-[var(--color-border-soft)] pt-4 text-lg font-semibold text-[var(--color-secondary-900)]">
                    <span>Total</span>
                    <span>BDT {{ number_format($summary['total'], 0) }}</span>
                </div>
            </div>

            <p class="mt-5 rounded-xl border border-[rgba(0,48,73,0.08)] bg-[rgba(0,48,73,0.025)] px-4 py-3 text-sm leading-6 text-[var(--color-secondary-900)]">Personalized Nikah details carry into checkout and fulfillment automatically.</p>

            <div class="mt-5 space-y-3">
                <a href="{{ route('checkout.show') }}" class="button-primary w-full">Proceed to checkout</a>
                <a href="{{ route('shop.index') }}" class="button-ghost w-full">Keep browsing</a>
            </div>
        </div>
    </aside>
</x-layouts.checkout>
