<x-layouts.narrow title="Wishlist | Azraq Bridal">
    <div class="space-y-6">
        <section class="surface-card-featured p-8">
            <span class="eyebrow">Wishlist</span>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[var(--color-secondary-900)]">Saved pieces and ideas</h1>
            <p class="mt-4 max-w-3xl text-base leading-8 text-[var(--color-text-soft)]">Keep a refined shortlist while comparing ceremonial products, personalized essentials, combos, and bookings.</p>
        </section>

        @if ($products->isEmpty())
            <section class="surface-card p-10 text-center">
                <h2 class="text-3xl font-semibold text-[var(--color-secondary-900)]">Your wishlist is still empty</h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-soft)]">Save pieces you want to revisit, then return here to compare categories, move favorites into the cart, or continue into curated collections.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('shop.index') }}" class="button-primary">Continue shopping</a>
                    <a href="{{ route('collections.show', 'gift-picks') }}" class="button-ghost">Browse gift picks</a>
                </div>
            </section>
        @else
            <div class="grid gap-5">
                @foreach ($products as $product)
                    @php($primaryImage = $product->images->firstWhere('is_primary', true) ?: $product->images->first())
                    @php($wishlistImage = $product->storefront_preview_image_url)
                    @php($defaultVariant = $product->variants->firstWhere('is_default', true) ?: $product->variants->first())
                    <article class="surface-card p-6">
                        <div class="flex flex-col gap-5 md:flex-row md:items-start">
                            <a href="{{ route('products.show', $product) }}" class="block h-36 overflow-hidden rounded-[var(--radius-xl)] bg-[var(--color-surface-cream)] md:w-36">
                                @if ($wishlistImage)
                                    <img src="{{ $wishlistImage }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                @endif
                            </a>

                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="eyebrow">{{ $product->type?->label() }}</span>
                                    @if ($product->category)
                                        <x-storefront.trust-badge :label="$product->category->name" />
                                    @endif
                                </div>

                                <h2 class="mt-4 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ $product->name }}</h2>
                                <p class="mt-3 text-sm leading-8 text-[var(--color-text-soft)]">{{ $product->excerpt ?: $product->description }}</p>
                                <p class="mt-4 text-lg font-semibold text-[var(--color-secondary-900)]">BDT {{ number_format((float) $product->price, 0) }}</p>

                                <div class="mt-5 flex flex-wrap gap-3">
                                    <form method="POST" action="{{ route('cart.store', $product) }}">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1">
                                        @if ($defaultVariant)
                                            <input type="hidden" name="variant_id" value="{{ $defaultVariant->id }}">
                                        @endif
                                        <button type="submit" class="button-primary">Move to cart</button>
                                    </form>
                                    <a href="{{ route('products.show', $product) }}" class="button-ghost">View details</a>
                                    <form method="POST" action="{{ route('wishlist.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button-ghost">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.narrow>
