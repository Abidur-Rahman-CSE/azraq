@php($primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first())
@php($galleryImages = $product->images->take(4))

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
            <x-storefront.product-breadcrumbs :product="$product" />

            <div class="mt-6 overflow-hidden rounded-[var(--radius-3xl)] border border-[var(--color-border-soft)] bg-[var(--color-surface-cream)]">
                @if ($primaryImage)
                    <img src="{{ $primaryImage->image_url }}" alt="{{ $primaryImage->label ?: $product->name }}" class="h-[420px] w-full object-cover" fetchpriority="high" decoding="async">
                @else
                    <div class="flex h-[420px] items-center justify-center text-[var(--color-text-soft)]">Personalized preview</div>
                @endif
            </div>

            @if ($galleryImages->count() > 1)
                <div class="mt-4 grid grid-cols-3 gap-3">
                    @foreach ($galleryImages as $image)
                        <div class="overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80">
                            <img src="{{ $image->image_url }}" alt="{{ $image->label ?: $product->name }}" class="h-24 w-full object-cover" loading="lazy" decoding="async">
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 rounded-[var(--radius-xl)] bg-white/80 p-4 text-sm leading-7 text-[var(--color-text-soft)]">
                Light personalization keeps this page fast and clean: one compact text input, optional variant selection, and no heavy proof-builder workflow.
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
                <span class="eyebrow">Light customization</span>
                @if ($product->category)
                    <x-storefront.trust-badge :label="$product->category->name" />
                @endif
                <x-storefront.trust-badge :label="$product->manage_stock ? ($product->stock_quantity.' available') : 'Made to order'" />
            </div>

            <h1 class="mt-5 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">{{ $product->name }}</h1>
            <p class="mt-4 text-base leading-8 text-[var(--color-text-soft)]">{{ $product->description }}</p>

            <div class="mt-6">
                <x-storefront.price-block :product="$product" />
            </div>

            <form method="POST" action="{{ route('cart.store', $product) }}" class="mt-8 space-y-6">
                @csrf

                <div class="surface-configurator p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-[var(--color-secondary-900)]">Personalize this detail</h2>
                        <span class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Simple add-on flow</span>
                    </div>

                    <label class="mt-5 block space-y-2">
                        <span class="text-sm font-semibold text-[var(--color-secondary-900)]">Engraving or short text</span>
                        <input type="text" name="custom_text" value="{{ old('custom_text') }}" maxlength="120" placeholder="Enter a short name or message" class="field-input">
                        <span class="text-xs text-[var(--color-text-soft)]">Keep this concise for compact items like pens and gifting add-ons. Maximum 120 characters.</span>
                    </label>
                </div>

                @if ($product->variants->isNotEmpty())
                    <div class="space-y-3">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Choose a finish</p>
                        <x-storefront.variant-pills :variants="$product->variants" />
                    </div>
                @endif

                <x-storefront.quantity-selector />

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="button-primary">Add personalized item</button>
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

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-4 text-sm text-[var(--color-secondary-900)]">
                    Order type: quick personalization with no proof-heavy layout review.
                </div>
                <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-4 text-sm text-[var(--color-secondary-900)]">
                    Best for ceremonial signing tables, gift boxes, and add-on pairings with Nikah products.
                </div>
            </div>
        </div>

        <div class="surface-card p-8">
            <h2 class="text-xl font-semibold text-[var(--color-secondary-900)]">Why this page is lighter</h2>
            <div class="mt-5 space-y-3 text-sm leading-7 text-[var(--color-text-soft)]">
                <p>This experience is intentionally simpler than the Nikah Nama configurator.</p>
                <p>You can add one short text input, choose a finish if offered, and move directly into cart without a full proof-layout workflow.</p>
            </div>
        </div>

        @if ($product->relatedProducts->isNotEmpty())
            <div class="surface-card-featured p-8">
                <h2 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Pair this with your Nikah order</h2>
                <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">Small add-ons work best when they travel with a broader ceremonial set.</p>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @foreach ($product->relatedProducts->take(4) as $relatedProduct)
                        <x-storefront.listing-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.product-detail>
