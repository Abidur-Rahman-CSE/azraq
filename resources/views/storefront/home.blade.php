@php
    $heroImage = $featuredProducts->first()?->storefront_preview_image_url;
@endphp

<x-layouts.storefront
    title="Azraq Bridal | Storefront"
    canonical="{{ route('home') }}"
    :social-image="$heroImage"
    :schema-data="[
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('brand.name'),
            'url' => route('home'),
        ],
    ]"
>
    <section class="section-shell overflow-hidden">
        <div class="container-shell grid items-center gap-10 lg:grid-cols-[1.02fr_0.98fr]">
            <div>
                <span class="eyebrow">{{ $homepageSections['hero']->subtitle ?? 'Homepage hero' }}</span>
                <h1 class="mt-6 max-w-3xl text-5xl font-bold tracking-tight text-[var(--text-main)] sm:text-7xl">
                    {{ $homepageSections['hero']->title ?? 'A browseable Azraq Bridal storefront is now layered onto the catalog architecture.' }}
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-[var(--text-muted)]">
                    {{ $homepageSections['hero']->content ?? 'The storefront now has real shop, category, and collection browsing powered by the Phase 1 catalog models, with warm brand styling, reusable cards, and filterable product discovery ready for PDP work next.' }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    @foreach (config('brand.trust_badges', []) as $badge)
                        <x-storefront.trust-badge :label="$badge" />
                    @endforeach
                </div>

                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="{{ $homepageSections['hero']->cta_href ?? route('shop.index') }}" class="button-primary">{{ $homepageSections['hero']->cta_label ?? 'Browse the shop' }}</a>
                    <a href="#collections" class="button-secondary">See featured collections</a>
                </div>
            </div>

            <div class="surface-card-featured p-5 sm:p-6 lg:p-8">
                <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="relative overflow-hidden rounded-[var(--radius-3xl)] bg-[var(--bg-section-soft)]">
                        @if ($heroImage)
                            <img src="{{ $heroImage }}" alt="Featured Azraq Bridal product collage" class="h-full min-h-[420px] w-full object-cover">
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-[rgba(26,28,42,0.72)] via-[rgba(26,28,42,0.08)] to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-7">
                            @php($leadProduct = $featuredProducts->first())
                            @if ($leadProduct)
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/75">{{ $leadProduct->type?->label() }}</p>
                                <h2 class="mt-3 font-serif text-3xl font-semibold leading-tight text-white sm:text-4xl">{{ $leadProduct->name }}</h2>
                                <p class="mt-3 max-w-md text-sm leading-7 text-white/80">{{ \Illuminate\Support\Str::limit($leadProduct->excerpt ?: strip_tags($leadProduct->description), 96) }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-4">
                        @foreach ($featuredProducts->slice(1, 2) as $product)
                            @php($productImage = $product->storefront_preview_image_url)
                            <a href="{{ route('products.show', $product) }}" class="group relative overflow-hidden rounded-[var(--radius-2xl)] bg-[var(--bg-section-soft)] min-h-[202px]">
                                @if ($productImage)
                                    <img src="{{ $productImage }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-[rgba(26,28,42,0.74)] via-[rgba(26,28,42,0.12)] to-transparent"></div>
                                <div class="absolute inset-x-0 bottom-0 p-5">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/72">{{ $product->category?->name ?: $product->type?->label() }}</p>
                                    <h3 class="mt-2 text-xl font-semibold leading-tight text-white">{{ $product->name }}</h3>
                                    <div class="mt-3 flex items-center justify-between gap-3 text-sm text-white/78">
                                        <span>BDT {{ number_format((float) $product->price, 0) }}</span>
                                        <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] backdrop-blur-sm">View</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-shell pt-0">
        <div class="container-shell">
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['title' => 'Handcrafted finishing', 'copy' => 'Refined materials, polished detailing, and presentation-ready finishing for ceremonial gifting.'],
                    ['title' => 'Personalized proof support', 'copy' => 'Nikah Nama orders stay elegant and accurate with proof-aware review before production.'],
                    ['title' => 'Giftable premium presentation', 'copy' => 'Framing, keepsakes, and bridal pieces are merchandised to feel collectible, not generic.'],
                ] as $highlight)
                    <article class="surface-card overflow-hidden p-0">
                        <div class="h-2 bg-[linear-gradient(90deg,var(--accent-primary),rgba(187,145,92,0.22))]"></div>
                        <div class="p-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--accent-primary)]">{{ $highlight['title'] }}</p>
                            <p class="mt-4 text-sm leading-7 text-[var(--text-muted)]">{{ $highlight['copy'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="catalog" class="section-shell pt-0">
        <div class="container-shell">
            <x-storefront.section-header
                :eyebrow="$homepageSections['featured_categories']->subtitle ?? 'Featured categories'"
                :title="$homepageSections['featured_categories']->title ?? 'Browse the main Azraq Bridal catalog groups.'"
                :description="$homepageSections['featured_categories']->content ?? 'These category tiles now come from the actual catalog tables, so future CMS reordering and merchandising can build on the same data source the storefront uses.'"
            />

            <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($featuredCategories as $category)
                    <x-storefront.category-tile :category="$category" />
                @endforeach
            </div>
        </div>
    </section>

    @if ($signatureNikah)
        <section class="section-shell bg-white/55">
            <div class="container-shell">
                <div class="surface-card-featured grid gap-8 p-8 lg:grid-cols-[0.95fr_1.05fr] lg:p-10">
                    <div class="overflow-hidden rounded-[var(--radius-3xl)] bg-[var(--bg-section-soft)]">
                        @php($nikahImage = $signatureNikah->storefront_preview_image_url)
                        @if ($nikahImage)
                            <img src="{{ $nikahImage }}" alt="{{ $signatureNikah->name }}" class="h-full min-h-[430px] w-full object-cover">
                        @endif
                    </div>
                    <div class="flex flex-col justify-center">
                        <span class="eyebrow">Signature Nikah highlight</span>
                        <h2 class="mt-5 text-4xl font-semibold text-[var(--text-main)] sm:text-5xl">{{ $signatureNikah->name }}</h2>
                        <p class="mt-5 text-lg leading-8 text-[var(--text-muted)]">{{ $signatureNikah->description }}</p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <x-storefront.trust-badge label="Template-driven personalization" />
                            <x-storefront.trust-badge label="Curated font selection" />
                            <x-storefront.trust-badge label="Proof-aware order flow" />
                        </div>
                        <div class="mt-8 grid gap-3 sm:grid-cols-3">
                            @foreach ([
                                'Fill structured ceremonial details',
                                'Choose a premium typography direction',
                                'Submit proof notes before fulfillment',
                            ] as $step)
                                <div class="rounded-[var(--radius-xl)] border border-[var(--border-soft)] bg-white/78 p-4 text-sm leading-7 text-[var(--text-main)]">{{ $step }}</div>
                            @endforeach
                        </div>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="{{ route('products.show', $signatureNikah) }}" class="button-primary">Customize your Nikah order</a>
                            <a href="{{ route('categories.show', $signatureNikah->category) }}" class="button-ghost">Explore Nikah Collection</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section id="architecture" class="section-shell bg-white/55">
        <div class="container-shell">
            <x-storefront.section-header
                :eyebrow="$homepageSections['featured_products']->subtitle ?? 'Featured products'"
                :title="$homepageSections['featured_products']->title ?? 'Storefront discovery now highlights live featured products from different product types.'"
                :description="$homepageSections['featured_products']->content ?? 'This keeps the separation between standard, light customizable, advanced personalized, bundle, and service products visible in the UI instead of burying that logic only in the database.'"
            />

            <div class="mt-10 grid gap-6 lg:grid-cols-2 xl:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-storefront.listing-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>

    <section id="collections" class="section-shell">
        <div class="container-shell">
            <div class="surface-card grid gap-8 p-8 lg:grid-cols-[1fr_0.9fr] lg:p-10">
                <div>
                    <span class="eyebrow">Featured collections</span>
                    <h2 class="mt-4 text-3xl font-semibold text-[var(--text-main)]">{{ $homepageSections['featured_collections']->title ?? 'Collections are now first-class browsing routes, not just admin labels.' }}</h2>
                    <p class="mt-4 text-base leading-8 text-[var(--text-muted)]">
                        {{ $homepageSections['featured_collections']->content ?? 'This phase lays the groundwork for best-sellers pages, combo landing pages, personalized gift edits, and curated merchandising blocks without duplicating storefront query logic.' }}
                    </p>
                    <div class="mt-8">
                        <a href="{{ $homepageSections['featured_collections']->cta_href ?? route('shop.index') }}" class="button-primary">{{ $homepageSections['featured_collections']->cta_label ?? 'Open full shop' }}</a>
                    </div>
                </div>

                <div class="grid gap-4">
                    @foreach ($featuredCollections as $collection)
                        <x-storefront.collection-card :collection="$collection" />
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @if ($comboSpotlight->isNotEmpty())
        <section class="section-shell bg-white/55">
            <div class="container-shell">
                <x-storefront.section-header
                    eyebrow="Combo spotlight"
                    title="Curated bundles designed to feel gift-ready, ceremonial, and easy to order."
                    description="Package pages should feel visually grouped and savings-aware, so the homepage now teases combo value directly."
                />

                <div class="mt-10 grid gap-6 lg:grid-cols-3">
                    @foreach ($comboSpotlight as $combo)
                        <x-storefront.listing-card :product="$combo" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($bridalWearSpotlight->isNotEmpty() || $bookingHighlights->isNotEmpty())
        <section class="section-shell">
            <div class="container-shell grid gap-8 xl:grid-cols-[1.05fr_0.95fr]">
                @if ($bridalWearSpotlight->isNotEmpty())
                    <div class="surface-card p-8 lg:p-10">
                        <span class="eyebrow">Bridal wear spotlight</span>
                        <h2 class="mt-4 text-3xl font-semibold text-[var(--text-main)]">Customized bridal wear with soft editorial presentation.</h2>
                        <div class="mt-8 grid gap-5">
                            @foreach ($bridalWearSpotlight as $product)
                                <x-storefront.listing-card :product="$product" />
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($bookingHighlights->isNotEmpty())
                    <div class="surface-card-featured p-8 lg:p-10">
                        <span class="eyebrow">Booking / mehendi services</span>
                        <h2 class="mt-4 text-3xl font-semibold text-[var(--text-main)]">Service-led experiences deserve a gentler, inquiry-first storefront presence.</h2>
                        <p class="mt-4 text-base leading-8 text-[var(--text-muted)]">Bridal, non-bridal, and mehendi bookings are highlighted separately so they do not feel like stock-first products.</p>
                        <div class="mt-8 grid gap-4">
                            @foreach ($bookingHighlights as $service)
                                @php($serviceImage = $service->storefront_preview_image_url)
                                <a href="{{ route('products.show', $service) }}" class="surface-card block overflow-hidden p-0 transition hover:-translate-y-1 hover:shadow-[var(--shadow-medium)]">
                                    <div class="grid gap-0 md:grid-cols-[190px_1fr]">
                                        <div class="bg-[var(--bg-section-soft)]">
                                            @if ($serviceImage)
                                                <img src="{{ $serviceImage }}" alt="{{ $service->name }}" class="h-full min-h-[200px] w-full object-cover">
                                            @else
                                                <div class="h-full min-h-[200px] w-full bg-[radial-gradient(circle_at_top,_rgba(187,145,92,0.18),_transparent_50%),linear-gradient(180deg,rgba(255,255,255,0.9),rgba(244,237,228,0.84))]"></div>
                                            @endif
                                        </div>
                                        <div class="p-5">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-[var(--accent-primary)]">{{ $service->type?->label() }}</p>
                                                    <h3 class="mt-2 text-2xl font-semibold text-[var(--text-main)]">{{ $service->name }}</h3>
                                                    <p class="mt-2 text-sm leading-7 text-[var(--text-muted)]">{{ \Illuminate\Support\Str::limit($service->excerpt ?: strip_tags($service->description), 120) }}</p>
                                                </div>
                                                <span class="button-pill">Inquire</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if ($testimonials->isNotEmpty())
        <section class="section-shell bg-white/55">
            <div class="container-shell">
                <x-storefront.section-header
                    eyebrow="Testimonials"
                    title="Customer notes that make the storefront feel trustworthy, not just polished."
                    description="Social proof stays light and elegant, keeping the focus on the bridal tone rather than looking like a dense review wall."
                />

                <div class="mt-10 grid gap-6 lg:grid-cols-3">
                    @foreach ($testimonials as $review)
                        <x-storefront.review-card :review="$review" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($faqPreview->isNotEmpty() && ($homepageSections['faq_preview']->is_enabled ?? true))
        <section class="section-shell bg-white/55">
            <div class="container-shell">
                <x-storefront.section-header
                    :eyebrow="$homepageSections['faq_preview']->subtitle ?? 'FAQ preview'"
                    :title="$homepageSections['faq_preview']->title ?? 'Frequently asked questions'"
                    :description="$homepageSections['faq_preview']->content ?? 'Bring delivery, personalization, and proof expectations into the homepage for better conversion clarity.'"
                />

                <div class="mt-10 grid gap-4">
                    @foreach ($faqPreview as $faq)
                        <article class="surface-card p-6">
                            <h3 class="text-xl font-semibold text-[var(--text-main)]">{{ $faq->question }}</h3>
                            <p class="mt-4 text-sm leading-7 text-[var(--text-muted)]">{{ $faq->answer }}</p>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">
                    <a href="{{ $homepageSections['faq_preview']->cta_href ?? route('faq.index') }}" class="button-primary">{{ $homepageSections['faq_preview']->cta_label ?? 'Read all FAQs' }}</a>
                </div>
            </div>
        </section>
    @endif
</x-layouts.storefront>
