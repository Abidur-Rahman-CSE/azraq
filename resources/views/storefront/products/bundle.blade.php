@php($primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first())
@php($bundleItems = $product->bundleItems->filter(fn ($item) => $item->childProduct))
@php($bundleReferencePrice = max((float) ($product->compare_at_price ?: 0), (float) $bundleValue))
@php($bundleSavings = max(0, $bundleReferencePrice - (float) $product->price))

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
    <div class="space-y-6 lg:sticky lg:top-28 lg:self-start">
        <div class="surface-product overflow-hidden p-6">
            <x-storefront.product-breadcrumbs :product="$product" />

            <div class="mt-6 rounded-[var(--radius-3xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)] p-6">
                <div class="grid gap-4">
                    <div class="overflow-hidden rounded-[var(--radius-2xl)] bg-white">
                        @if ($primaryImage)
                            <img src="{{ $primaryImage->image_url }}" alt="{{ $primaryImage->label ?: $product->name }}" class="h-[360px] w-full object-cover" fetchpriority="high" decoding="async">
                        @else
                            <div class="flex h-[360px] items-center justify-center text-[var(--color-text-soft)]">Combo presentation preview</div>
                        @endif
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($bundleItems->take(2) as $bundleItem)
                            @php($itemImage = $bundleItem->childProduct->images->firstWhere('is_primary', true) ?: $bundleItem->childProduct->images->first())
                            <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/85 p-4">
                                <p class="text-xs uppercase tracking-[0.16em] text-[var(--color-text-soft)]">Included item</p>
                                <div class="mt-3 flex items-center gap-3">
                                    <div class="h-16 w-16 overflow-hidden rounded-[var(--radius-lg)] bg-[var(--color-surface-cream)]">
                                        @if ($itemImage)
                                            <img src="{{ $itemImage->image_url }}" alt="{{ $bundleItem->childProduct->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $bundleItem->childProduct->name }}</p>
                                        <p class="text-xs text-[var(--color-text-soft)]">Qty {{ $bundleItem->quantity }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if ($recentlyViewed->isNotEmpty())
            <div class="surface-card-soft p-6">
                <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Recently viewed</h2>
                <div class="mt-5 grid gap-4">
                    @foreach ($recentlyViewed as $recentProduct)
                        <x-storefront.listing-card :product="$recentProduct" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="surface-sidebar p-8">
            <div class="flex flex-wrap items-center gap-3">
                <span class="eyebrow">Curated combo</span>
                @if ($product->category)
                    <x-storefront.trust-badge :label="$product->category->name" />
                @endif
                @if ($bundleSavings > 0)
                    <x-storefront.trust-badge :label="'Save BDT '.number_format($bundleSavings, 0)" />
                @endif
            </div>

            <h1 class="mt-5 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">{{ $product->name }}</h1>
            <p class="mt-4 text-base leading-8 text-[var(--color-text-soft)]">{{ $product->description }}</p>

            <div class="mt-6 rounded-[var(--radius-2xl)] bg-white/80 p-5">
                <x-storefront.price-block :product="$product" />
                @if ($bundleReferencePrice > 0)
                    <p class="mt-3 text-sm text-[var(--color-text-soft)]">Combined standalone value: BDT {{ number_format($bundleReferencePrice, 0) }}</p>
                @endif
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Includes</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ $bundleItems->sum('quantity') }} curated pieces</p>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Occasion</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">Giftable Nikkah set</p>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Format</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">Add full combo at once</p>
                </div>
            </div>

            <form method="POST" action="{{ route('cart.store', $product) }}" class="mt-8 space-y-6">
                @csrf
                <x-storefront.quantity-selector />

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="button-primary">Add full combo</button>
                    <a href="{{ route('checkout.show') }}" class="button-secondary">Buy now</a>
                </div>
            </form>

            <div class="mt-4 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('wishlist.store', $product) }}">
                    @csrf
                    <button type="submit" class="button-ghost">Save combo</button>
                </form>
                <a href="{{ route('shop.index', ['type' => 'bundle']) }}" class="button-ghost">Explore more combos</a>
            </div>

            <div class="mt-6 rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] p-5 text-sm leading-7 text-[var(--color-secondary-900)]">
                This package is curated to feel ceremonial and giftable, not just discounted. Each included item is selected to create a more complete Nikkah table or presentation set.
            </div>
        </div>

        <div class="surface-card p-8">
            <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Everything in this combo</h2>
            <div class="mt-6 grid gap-4">
                @foreach ($bundleItems as $bundleItem)
                    @php($child = $bundleItem->childProduct)
                    @php($itemImage = $child->images->firstWhere('is_primary', true) ?: $child->images->first())
                    <article class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5">
                        <div class="flex flex-col gap-5 sm:flex-row">
                            <div class="h-28 w-full overflow-hidden rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] sm:w-28">
                                @if ($itemImage)
                                    <img src="{{ $itemImage->image_url }}" alt="{{ $child->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-lg font-semibold text-[var(--color-secondary-900)]">{{ $child->name }}</h3>
                                    <x-storefront.trust-badge :label="'Qty '.$bundleItem->quantity" />
                                    <x-storefront.trust-badge :label="$child->type?->label()" />
                                </div>
                                <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">{{ $child->excerpt ?: $child->description }}</p>
                                <div class="mt-4 flex flex-wrap items-center gap-3">
                                    <p class="text-sm font-semibold text-[var(--color-secondary-900)]">BDT {{ number_format((float) $child->price, 0) }}</p>
                                    <a href="{{ route('products.show', $child) }}" class="text-sm font-semibold text-[var(--color-primary-900)]">View item</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="surface-card p-8">
            <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Who this is for</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5 text-sm leading-7 text-[var(--color-text-soft)]">
                    Customers who want a complete ceremonial starter set without assembling each item separately.
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5 text-sm leading-7 text-[var(--color-text-soft)]">
                    Gifting scenarios where value, cohesion, and presentation matter as much as the raw item count.
                </div>
            </div>
        </div>

        @if ($product->relatedProducts->isNotEmpty())
            <div class="surface-card-featured p-8">
                <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Related combos or individual pieces</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @foreach ($product->relatedProducts->take(4) as $relatedProduct)
                        <x-storefront.listing-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.product-detail>
