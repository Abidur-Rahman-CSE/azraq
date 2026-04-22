<section class="lg:col-span-2 space-y-10 pt-10 text-[#3D3730]">
    <div class="grid gap-8 rounded-[1.75rem] border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_16px_rgba(0,0,0,0.06)] lg:grid-cols-[0.9fr_1.1fr]">
        @if ($storyVisual)
            <div class="overflow-hidden rounded-[1.25rem] border border-[#E8E3DC] bg-[#F5F2EC]">
                <img src="{{ $storyVisual }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
            </div>
        @endif

        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#C4A882]">Product story</p>
            <h2 class="mt-3 font-serif text-3xl font-semibold text-[#2C2C3E]">{{ $product->is_customizable ? 'A keepsake designed for ceremonial display' : 'A refined detail for your bridal collection' }}</h2>
            <div class="prose prose-sm mt-5 max-w-none prose-p:text-[#8C7F74] prose-headings:text-[#2C2C3E]">
                {!! $product->description !!}
            </div>
        </div>
    </div>

    @if ($product->is_customizable)
        <div class="rounded-[1.75rem] border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_16px_rgba(0,0,0,0.06)]">
            <h2 class="font-serif text-3xl font-semibold text-[#2C2C3E]">What’s included</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Personalised certificate', 'copy' => 'Digital proof plus final printed keepsake'],
                    ['label' => 'Digital proof review', 'copy' => 'Designer-reviewed proof before production'],
                    ['label' => 'Premium print', 'copy' => 'Printed on a rich 250gsm paper stock'],
                    ['label' => 'Framing upgrade', 'copy' => 'Optional presentation-ready framing add-on'],
                ] as $item)
                    <div class="rounded-xl bg-[#FAF8F5] p-5">
                        <p class="text-sm font-semibold text-[#2C2C3E]">{{ $item['label'] }}</p>
                        <p class="mt-2 text-sm leading-7 text-[#8C7F74]">{{ $item['copy'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-[1.75rem] border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_16px_rgba(0,0,0,0.06)]">
            <h2 class="font-serif text-3xl font-semibold text-[#2C2C3E]">How it works</h2>
            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                @foreach ([
                    ['step' => 'Step 1', 'title' => 'Enter your details', 'copy' => 'Add names, date, venue, and any proof notes.'],
                    ['step' => 'Step 2', 'title' => 'Review your digital proof', 'copy' => 'See your details update live and confirm the scene preview.'],
                    ['step' => 'Step 3', 'title' => 'Approve and we print', 'copy' => 'Once approved, we print, finish, and package your order.'],
                ] as $item)
                    <div class="rounded-xl border border-[#E8E3DC] bg-[#FAF8F5] p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#C4A882]">{{ $item['step'] }}</p>
                        <h3 class="mt-3 text-lg font-semibold text-[#2C2C3E]">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm leading-7 text-[#8C7F74]">{{ $item['copy'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="rounded-[1.75rem] border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_16px_rgba(0,0,0,0.06)]">
        <h2 class="font-serif text-3xl font-semibold text-[#2C2C3E]">Shipping, care, and policy</h2>
        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            @foreach ($deliveryRows as $row)
                <div class="flex items-start justify-between gap-4 border-b border-[#E8E3DC] py-3 text-sm">
                    <span class="font-medium text-[#2C2C3E]">{{ $row['label'] }}</span>
                    <span class="text-right text-[#8C7F74]">{{ $row['value'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-[1.75rem] border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_16px_rgba(0,0,0,0.06)]" x-data="{ open: 0 }">
        <h2 class="font-serif text-3xl font-semibold text-[#2C2C3E]">FAQ</h2>
        <div class="mt-6 divide-y divide-[#E8E3DC]">
            @foreach ($faqs as $index => $faq)
                <div class="py-4">
                    <button type="button" class="flex w-full items-center justify-between gap-3 text-left" @click="open = open === {{ $index }} ? null : {{ $index }}">
                        <span class="text-base font-semibold text-[#2C2C3E]">{{ $faq->question }}</span>
                        <span class="text-[#8C7F74]" x-text="open === {{ $index }} ? '−' : '+'"></span>
                    </button>
                    <div x-show="open === {{ $index }}" x-transition.duration.200ms>
                        <p class="pt-3 text-sm leading-7 text-[#8C7F74]">{{ $faq->answer }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if ($relatedProducts->isNotEmpty())
        <div>
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-serif text-3xl font-semibold text-[#2C2C3E]">You might also like</h2>
                <a href="{{ route('shop.index') }}" class="text-sm font-medium text-[#8B2635]">Browse all</a>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($relatedProducts->take(4) as $relatedProduct)
                    <x-storefront.listing-card :product="$relatedProduct" />
                @endforeach
            </div>
        </div>
    @endif

    @if ($product->reviews->isNotEmpty())
        <div class="rounded-[1.75rem] border border-[#E8E3DC] bg-white p-8 shadow-[0_2px_16px_rgba(0,0,0,0.06)]">
            <h2 class="font-serif text-3xl font-semibold text-[#2C2C3E]">What customers say</h2>
            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                @foreach ($product->reviews as $review)
                    <x-storefront.review-card :review="$review" />
                @endforeach
            </div>
        </div>
    @endif
</section>
