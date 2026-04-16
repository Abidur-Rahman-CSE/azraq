@php($primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first())
@php($galleryImages = $product->images->take(5))

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
                'availability' => $product->manage_stock && $product->stock_quantity <= 0
                    ? 'https://schema.org/OutOfStock'
                    : 'https://schema.org/InStock',
                'url' => route('products.show', $product),
            ],
        ],
    ]"
>
    <div class="space-y-6 lg:sticky lg:top-28 lg:self-start">
        <div class="surface-product overflow-hidden p-6">
            <div class="flex items-center justify-between gap-4">
                <x-storefront.product-breadcrumbs :product="$product" />
                <span class="hidden rounded-full bg-[var(--color-surface-cream)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-primary-900)] sm:inline-flex">
                    {{ $product->type?->label() }}
                </span>
            </div>

            <div class="mt-6 overflow-hidden rounded-[var(--radius-3xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)]">
                @if ($primaryImage)
                    <img src="{{ $primaryImage->image_url }}" alt="{{ $primaryImage->label ?: $product->name }}" class="h-[440px] w-full object-cover" fetchpriority="high" decoding="async">
                @else
                    <div class="flex h-[440px] items-center justify-center text-[var(--color-text-soft)]">Product gallery preview</div>
                @endif
            </div>

            @if ($galleryImages->count() > 1)
                <div class="mt-4 grid grid-cols-4 gap-3">
                    @foreach ($galleryImages as $image)
                        <div class="overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80">
                            <img src="{{ $image->image_url }}" alt="{{ $image->label ?: $product->name }}" class="h-24 w-full object-cover" loading="lazy" decoding="async">
                        </div>
                    @endforeach
                </div>
            @endif

            <p class="mt-4 text-sm text-[var(--color-text-soft)]">Editorial product imagery styled to help you assess tone, craftsmanship, and gifting presentation before checkout.</p>
        </div>

        @if ($recentlyViewed->isNotEmpty())
            <div class="surface-card-soft p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Recently viewed</h2>
                    <a href="{{ route('shop.index') }}" class="text-sm font-semibold text-[var(--color-primary-900)]">Continue browsing</a>
                </div>
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
                <span class="eyebrow">Ready to ship</span>
                @if ($product->category)
                    <x-storefront.trust-badge :label="$product->category->name" />
                @endif
                <x-storefront.trust-badge :label="$product->manage_stock ? ($product->stock_quantity > 0 ? 'In stock now' : 'Currently unavailable') : 'Made to order'" />
            </div>

            <h1 class="mt-5 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">{{ $product->name }}</h1>
            <p class="mt-4 max-w-2xl text-base leading-8 text-[var(--color-text-soft)]">{{ $product->excerpt ?: $product->description }}</p>

            <div class="mt-6">
                <x-storefront.price-block :product="$product" />
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Lead time</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">{{ max(2, $product->lead_time_days ?: 4) }} to {{ max(4, ($product->lead_time_days ?: 4) + 2) }} days</p>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Packaging</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">Gift-ready finishing</p>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Support</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--color-secondary-900)]">WhatsApp order help</p>
                </div>
            </div>

            <form method="POST" action="{{ route('cart.store', $product) }}" class="mt-8 space-y-6">
                @csrf

                @if ($product->variants->isNotEmpty())
                    <div class="space-y-3">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Choose a variant</p>
                        <x-storefront.variant-pills :variants="$product->variants" />
                    </div>
                @endif

                <x-storefront.quantity-selector />

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="button-primary">Add to cart</button>
                    <a href="{{ route('checkout.show') }}" class="button-secondary">Buy now</a>
                </div>
            </form>

            <div class="mt-4 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('wishlist.store', $product) }}">
                    @csrf
                    <button type="submit" class="button-ghost">Save to wishlist</button>
                </form>
                <a href="{{ route('products.show', $product) }}" class="button-ghost">Share link</a>
            </div>

            <div class="mt-6 rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] p-5 text-sm leading-7 text-[var(--color-secondary-900)]">
                Delivery note: each piece is packed with protective bridal wrapping and reviewed before dispatch for gifting presentation.
            </div>
        </div>

        <div class="surface-card p-8">
            <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Product details</h2>
            <p class="mt-4 text-sm leading-8 text-[var(--color-text-soft)]">{{ $product->description }}</p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Specifications</p>
                    <div class="mt-3 space-y-2 text-sm text-[var(--color-secondary-900)]">
                        <p>SKU: {{ $product->sku }}</p>
                        <p>Category: {{ $product->category?->name ?: 'Azraq Bridal' }}</p>
                        <p>Type: {{ $product->type?->label() }}</p>
                    </div>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-5">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Best suited for</p>
                    <div class="mt-3 space-y-2 text-sm text-[var(--color-secondary-900)]">
                        <p>Wedding gifting and ceremonial styling</p>
                        <p>Category-led bridal set building</p>
                        <p>Elegant add-ons for curated orders</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="surface-card p-8">
            <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Shipping, care, and policy</h2>
            <div class="mt-5 space-y-4">
                <details class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/75 p-5" open>
                    <summary class="cursor-pointer text-sm font-semibold text-[var(--color-secondary-900)]">Shipping guidance</summary>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Orders are packed with protective presentation wrapping and dispatched according to the product lead time shown above.</p>
                </details>
                <details class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/75 p-5">
                    <summary class="cursor-pointer text-sm font-semibold text-[var(--color-secondary-900)]">Care notes</summary>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Store delicate bridal pieces away from direct moisture and perfumes, and keep them folded or boxed between events.</p>
                </details>
                <details class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/75 p-5">
                    <summary class="cursor-pointer text-sm font-semibold text-[var(--color-secondary-900)]">Returns and review</summary>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Azraq reviews each order before dispatch. Return handling depends on product condition and whether the order includes personalized elements.</p>
                </details>
            </div>
        </div>

        @if ($product->relatedProducts->isNotEmpty())
            <div class="surface-card-featured p-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow">Suggested next</p>
                        <h2 class="mt-4 text-2xl font-semibold text-[var(--color-secondary-900)]">You may also like</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-7 text-[var(--color-text-soft)]">A soft recommendation flow built around matching categories, gifting logic, and bridal set completion.</p>
                    </div>
                    <a href="{{ route('shop.index') }}" class="button-ghost">Explore more</a>
                </div>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @foreach ($product->relatedProducts->take(4) as $relatedProduct)
                        <x-storefront.listing-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </div>
        @endif

        @if ($product->reviews->isNotEmpty())
            <div class="surface-card p-8">
                <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Customer reviews</h2>
                <div class="mt-5 grid gap-4">
                    @foreach ($product->reviews as $review)
                        <x-storefront.review-card :review="$review" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.product-detail>
