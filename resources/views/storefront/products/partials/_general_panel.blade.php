<section class="space-y-4 text-[#3D3730]">
    <div class="rounded-[1.5rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] sm:p-7">
        <div class="flex flex-wrap gap-2">
            @foreach ($badgeItems as $badge)
                <span class="rounded-full bg-[#FAF8F5] px-3 py-1 text-xs font-semibold text-[#8B2635]">{{ $badge }}</span>
            @endforeach
        </div>

        <h1 class="mt-4 font-serif text-3xl font-semibold text-[#2C2C3E]">{{ $product->name }}</h1>

        <div class="mt-4 flex flex-wrap items-end gap-3">
            <p class="text-2xl font-semibold text-[#8B2635]">BDT {{ number_format((float) $product->price, 0) }}</p>
            @if ($product->compare_at_price)
                <p class="text-sm text-[#8C7F74] line-through">BDT {{ number_format((float) $product->compare_at_price, 0) }}</p>
            @endif
        </div>

        <p class="mt-4 text-sm leading-7 text-[#8C7F74]">{{ $shortDescription }}</p>
    </div>

    <form method="POST" action="{{ route('cart.store', $product) }}" class="space-y-4" x-ref="mainProductForm">
        @csrf

        @if ($product->variants->isNotEmpty())
            <div class="rounded-[1.25rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)]">
                <h2 class="font-serif text-2xl font-semibold text-[#2C2C3E]">Choose a variant</h2>
                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach ($product->variants as $variant)
                        <label class="cursor-pointer">
                            <input type="radio" name="variant_id" value="{{ $variant->id }}" class="sr-only" x-model="selectedVariant" @checked(old('variant_id', $product->variants->firstWhere('is_default', true)?->id) == $variant->id)>
                            <span
                                class="inline-flex rounded-full border px-4 py-3 text-sm font-medium transition duration-200 ease-out"
                                :class="selectedVariant === '{{ $variant->id }}' ? 'border-[#8B2635] bg-[#8B2635] text-white' : 'border-[#E8E3DC] bg-[#FAF8F5] text-[#3D3730]'"
                            >
                                {{ $variant->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('variant_id')
                    <p class="mt-3 text-sm font-medium text-red-700">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div class="rounded-[1.25rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)]">
            <h2 class="font-serif text-2xl font-semibold text-[#2C2C3E]">Quantity</h2>
            <div class="mt-5 inline-flex items-center rounded-xl border border-[#E8E3DC] bg-[#FAF8F5]">
                <button type="button" class="px-4 py-3 text-lg text-[#8B2635]" @click="quantity = Math.max(1, quantity - 1)">−</button>
                <input type="number" min="1" max="20" name="quantity" x-model="quantity" class="w-16 border-0 bg-transparent px-2 py-3 text-center text-base font-semibold text-[#2C2C3E] focus:outline-none focus:ring-0">
                <button type="button" class="px-4 py-3 text-lg text-[#8B2635]" @click="quantity = Math.min(20, quantity + 1)">+</button>
            </div>
            @error('quantity')
                <p class="mt-3 text-sm font-medium text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-[1.25rem] border border-[#E8E3DC] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)]" x-ref="ctaAnchor">
            <div class="space-y-3">
                <button type="submit" class="w-full rounded-xl bg-[#8B2635] px-5 py-4 text-base font-semibold text-white transition duration-200 ease-out hover:bg-[#6D1D29]">Add to cart</button>
                <button type="submit" name="buy_now" value="1" class="w-full rounded-xl border border-[#8B2635] px-5 py-4 text-base font-semibold text-[#8B2635] transition duration-200 ease-out hover:bg-[#FAF8F5]">Buy it now</button>
            </div>

            <div class="mt-5 grid gap-3 text-sm text-[#3D3730] sm:grid-cols-3">
                <div class="rounded-xl bg-[#FAF8F5] px-4 py-3">✓ Proof sent before production</div>
                <div class="rounded-xl bg-[#FAF8F5] px-4 py-3">✓ Secure checkout</div>
                <div class="rounded-xl bg-[#FAF8F5] px-4 py-3">✓ Carefully packaged & posted</div>
            </div>
        </div>
    </form>
</section>
