@php
    $primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
    $bundleItems = $product->bundleItems->filter(fn ($item) => $item->childProduct);
    $bundleReferencePrice = max((float) ($product->compare_at_price ?: 0), (float) $bundleValue);
    $bundleSavings = max(0, $bundleReferencePrice - (float) $product->price);
@endphp

<x-layouts.product-detail
    :title="$product->name.' | '.config('brand.name')"
    :description="$product->meta_description ?: ($product->excerpt ?: $product->description)"
    :social-image="$primaryImage?->image_url"
    :schema-data="[
        [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->meta_description ?: ($product->excerpt ?: $product->description),
            'image' => $product->images->pluck('image_url')->filter()->values()->all(),
            'sku' => $product->sku,
            'category' => $product->category?->name,
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'BDT',
                'price' => (float) $product->price,
                'availability' => 'https://schema.org/InStock',
                'url' => route('products.show', $product),
            ],
        ],
    ]"
>
    <div class="space-y-10">
        <section class="grid gap-8 lg:grid-cols-[minmax(0,55fr)_minmax(0,45fr)]">
            <div class="lg:self-stretch">
                <div class="space-y-4 lg:sticky lg:top-[88px]">
                    <div class="surface-product overflow-hidden p-4 sm:p-5">
                        <div class="mb-4">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-[var(--text-muted)]">Combo preview</p>
                            <p class="mt-1 text-sm font-medium text-[var(--text-main)]">{{ $bundleItems->sum('quantity') }} curated pieces</p>
                        </div>

                        <x-storefront.product-breadcrumbs :product="$product" />

                        <div class="mt-5 overflow-hidden rounded-xl border border-[var(--border-soft)] bg-[var(--bg-section-soft)]">
                            <div class="aspect-[4/5] w-full max-h-[58vh] lg:max-h-[500px]">
                                @if ($primaryImage)
                                    <img src="{{ $primaryImage->image_url }}" alt="{{ $primaryImage->label ?: $product->name }}" class="block h-full w-full object-cover" fetchpriority="high" decoding="async">
                                @else
                                    <div class="flex h-full min-h-[360px] items-center justify-center text-sm text-[var(--text-muted)]">Combo presentation preview</div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($bundleItems->take(2) as $bundleItem)
                                @php
                                    $child = $bundleItem->childProduct;
                                    $itemImage = $child->storefront_preview_image_url ?: ($child->images->firstWhere('is_primary', true)?->image_url ?: $child->images->first()?->image_url);
                                @endphp
                                <div class="rounded-xl border border-[var(--border-soft)] bg-white/85 p-4">
                                    <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Included item</p>
                                    <div class="mt-3 flex items-center gap-3">
                                        <div class="h-16 w-16 overflow-hidden rounded-lg bg-[var(--bg-section-soft)]">
                                            @if ($itemImage)
                                                <img src="{{ $itemImage }}" alt="{{ $child->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-[var(--text-main)]">{{ $child->name }}</p>
                                            <p class="text-xs text-[var(--text-muted)]">Qty {{ $bundleItem->quantity }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <section class="space-y-4 text-[var(--text-main)]">
                <div class="surface-card-featured p-5 sm:p-6">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-[var(--pill-bg)] px-2.5 py-0.5 text-[10px] font-medium text-[var(--accent-primary)]">Curated combo</span>
                        @if ($product->category)
                            <span class="rounded-full bg-[rgba(0,48,73,0.08)] px-2.5 py-0.5 text-[10px] font-medium text-[var(--accent-secondary)]">{{ $product->category->name }}</span>
                        @endif
                        @if ($bundleSavings > 0)
                            <span class="rounded-full bg-[rgba(120,0,0,0.08)] px-2.5 py-0.5 text-[10px] font-medium text-[var(--accent-primary)]">Save BDT {{ number_format($bundleSavings, 0) }}</span>
                        @endif
                    </div>

                    <h1 class="mt-2 font-serif text-[26px] font-semibold leading-tight text-[var(--text-main)]">{{ $product->name }}</h1>
                    <p class="mt-2 text-sm leading-relaxed text-[var(--text-muted)]">{{ $product->excerpt ?: $product->description }}</p>

                    <div class="mt-4">
                        <x-storefront.price-block :product="$product" />
                        @if ($bundleReferencePrice > 0)
                            <p class="mt-2 text-xs text-[var(--text-muted)]">Combined standalone value: BDT {{ number_format($bundleReferencePrice, 0) }}</p>
                        @endif
                    </div>
                </div>

                <div class="surface-card p-5">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-[var(--border-soft)] bg-white/85 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Includes</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--text-main)]">{{ $bundleItems->sum('quantity') }} pieces</p>
                        </div>
                        <div class="rounded-xl border border-[var(--border-soft)] bg-white/85 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Occasion</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--text-main)]">Nikkah set</p>
                        </div>
                        <div class="rounded-xl border border-[var(--border-soft)] bg-white/85 px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.16em] text-[var(--text-muted)]">Format</p>
                            <p class="mt-2 text-sm font-semibold text-[var(--text-main)]">One cart add</p>
                        </div>
                    </div>
                </div>

                <form id="order-form" method="POST" action="{{ route('cart.store', $product) }}" class="space-y-4" x-ref="mainOrderForm">
                    @csrf

                    <div class="surface-card p-5">
                        <h2 class="text-base font-semibold text-[var(--text-main)]">Quantity</h2>
                        <div class="mt-4">
                            <x-storefront.quantity-selector />
                        </div>
                    </div>

                    <div class="surface-card-featured p-5">
                        <button type="submit" class="button-primary w-full !rounded-[var(--radius-xl)] !py-4 !text-base">Add full combo</button>
                        <button type="submit" name="buy_now" value="1" class="button-ghost mt-2 w-full !rounded-[var(--radius-xl)] !py-3.5 !text-sm !text-[var(--accent-primary)]">Buy it now</button>
                    </div>
                </form>

                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('wishlist.store', $product) }}">
                        @csrf
                        <button type="submit" class="button-ghost">Save combo</button>
                    </form>
                    <a href="{{ route('shop.index', ['type' => 'bundle']) }}" class="button-ghost">Explore more combos</a>
                </div>
            </section>
        </section>

        <section class="space-y-6">
            <div class="surface-card p-8">
                <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,40fr)_minmax(0,60fr)]">
                    <div>
                        <p class="mb-3 text-xs uppercase tracking-[0.3em] text-[var(--accent-primary)]">Combo story</p>
                        <h2 class="font-serif text-2xl font-semibold leading-snug text-[var(--text-main)]">A complete ceremonial set without assembling every piece separately</h2>
                    </div>
                    <div class="space-y-4 text-sm leading-7 text-[var(--text-muted)]">
                        <p>{{ $product->description }}</p>
                        <p>This package is curated to feel ceremonial and giftable, not just discounted. Each included item is selected to create a more complete Nikkah table or presentation set.</p>
                    </div>
                </div>
            </div>

            <div class="surface-card p-8">
                <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Everything in this combo</h2>
                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    @foreach ($bundleItems as $bundleItem)
                        @php
                            $child = $bundleItem->childProduct;
                            $itemImage = $child->storefront_preview_image_url ?: ($child->images->firstWhere('is_primary', true)?->image_url ?: $child->images->first()?->image_url);
                        @endphp
                        <article class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5">
                            <div class="flex flex-col gap-5 sm:flex-row">
                                <div class="h-28 w-full overflow-hidden rounded-xl bg-[var(--bg-section-soft)] sm:w-28">
                                    @if ($itemImage)
                                        <img src="{{ $itemImage }}" alt="{{ $child->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-semibold text-[var(--text-main)]">{{ $child->name }}</h3>
                                        <span class="rounded-full bg-[var(--pill-bg)] px-2 py-0.5 text-[10px] font-medium text-[var(--accent-primary)]">Qty {{ $bundleItem->quantity }}</span>
                                        @if ($child->type)
                                            <span class="rounded-full bg-[rgba(0,48,73,0.08)] px-2 py-0.5 text-[10px] font-medium text-[var(--accent-secondary)]">{{ $child->type->label() }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-3 text-sm leading-7 text-[var(--text-muted)]">{{ $child->excerpt ?: $child->description }}</p>
                                    <div class="mt-4 flex flex-wrap items-center gap-3">
                                        <p class="text-sm font-semibold text-[var(--text-main)]">BDT {{ number_format((float) $child->price, 0) }}</p>
                                        <a href="{{ route('products.show', $child) }}" class="text-sm font-semibold text-[var(--accent-primary)] transition hover:underline">View item</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="surface-card p-8">
                <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Who this is for</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5 text-sm leading-7 text-[var(--text-muted)]">
                        Customers who want a complete ceremonial starter set without assembling each item separately.
                    </div>
                    <div class="rounded-xl border border-[var(--border-soft)] bg-white/80 p-5 text-sm leading-7 text-[var(--text-muted)]">
                        Gifting scenarios where value, cohesion, and presentation matter as much as the raw item count.
                    </div>
                </div>
            </div>
        </section>

        @if ($product->relatedProducts->isNotEmpty() || $recentlyViewed->isNotEmpty())
            <section class="space-y-6">
                @if ($product->relatedProducts->isNotEmpty())
                    <div class="surface-card p-8">
                        <div class="mb-6 flex items-center justify-between gap-4">
                            <h2 class="font-serif text-xl font-semibold text-[var(--text-main)]">Related combos or individual pieces</h2>
                            <a href="{{ route('shop.index', ['type' => 'bundle']) }}" class="text-sm text-[var(--accent-primary)] transition hover:underline">Browse combos</a>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($product->relatedProducts->take(4) as $relatedProduct)
                                <x-storefront.listing-card :product="$relatedProduct" />
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($recentlyViewed->isNotEmpty())
                    <div class="surface-card p-8">
                        <h2 class="mb-5 text-sm font-medium uppercase tracking-[0.3em] text-[var(--text-muted)]">Recently viewed</h2>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($recentlyViewed as $recentProduct)
                                <x-storefront.listing-card :product="$recentProduct" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        @endif
    </div>
</x-layouts.product-detail>
