@php
    $heroImage = data_get($homepageSections->get('hero'), 'settings.desktop_image_url')
        ?: $featuredProducts->first()?->storefront_preview_image_url;
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
    {{-- ── HERO ─────────────────────────────────────────────────── --}}
    <section class="section-shell overflow-hidden">
        <div class="container-shell grid items-center gap-10 lg:grid-cols-[1.02fr_0.98fr]">
            <div>
                <span class="section-kicker text-[0.68rem]">{{ $homepageSections['hero']->subtitle ?? 'Azraq Bridal' }}</span>
                <h1 class="mt-6 max-w-3xl text-5xl font-bold tracking-tight text-[var(--text-main)] sm:text-7xl">
                    {{ $homepageSections['hero']->title ?? 'A browseable Azraq Bridal storefront is now layered onto the catalog architecture.' }}
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-[var(--text-muted)]">
                    {{ $homepageSections['hero']->content ?? 'Premium bridal wear, Nikah essentials, giftable accessories, curated combos, and booking-led services — all in one calm, polished storefront.' }}
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

            <div class="glass-card-brand p-5 sm:p-6 lg:p-8">
                <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="relative overflow-hidden rounded-[var(--radius-3xl)] bg-[var(--bg-section-soft)]">
                        @if ($heroImage)
                            <img src="{{ $heroImage }}" alt="Featured Azraq Bridal product collage" class="h-full min-h-[420px] w-full object-cover">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-[rgba(7,14,24,0.72)] via-[rgba(7,14,24,0.08)] to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-7">
                            @php($leadProduct = $featuredProducts->first())
                            @if ($leadProduct)
                                <p class="section-kicker text-[0.6rem] text-white/70">{{ $leadProduct->type?->label() }}</p>
                                <h2 class="mt-3 text-3xl font-semibold leading-tight text-white sm:text-4xl">{{ $leadProduct->name }}</h2>
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
                                <div class="absolute inset-0 bg-gradient-to-t from-[rgba(7,14,24,0.74)] via-[rgba(7,14,24,0.12)] to-transparent"></div>
                                <div class="absolute inset-x-0 bottom-0 p-5">
                                    <p class="section-kicker text-[0.58rem] text-white/70">{{ $product->category?->name ?: $product->type?->label() }}</p>
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

    {{-- ── BRAND VALUES — glass cards on cream ─────────────────── --}}
    <section class="section-shell pt-0">
        <div class="container-shell">
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['icon' => '✦', 'title' => 'Handcrafted finishing', 'copy' => 'Refined materials, polished detailing, and presentation-ready finishing for ceremonial gifting.'],
                    ['icon' => '◈', 'title' => 'Personalized proof support', 'copy' => 'Nikah Nama orders stay elegant and accurate with proof-aware review before production.'],
                    ['icon' => '❋', 'title' => 'Premium presentation', 'copy' => 'Framing, keepsakes, and bridal pieces are merchandised to feel collectible, not generic.'],
                ] as $val)
                    <article class="brand-value-card">
                        <p class="text-2xl text-[var(--azraq-burgundy)] opacity-60">{{ $val['icon'] }}</p>
                        <p class="mt-4 text-sm font-semibold uppercase tracking-[0.18em] text-[var(--accent-primary)]">{{ $val['title'] }}</p>
                        <p class="mt-3 text-sm leading-7 text-[var(--text-muted)]">{{ $val['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── CATEGORIES ───────────────────────────────────────────── --}}
    <section id="catalog" class="section-shell pt-0">
        <div class="container-shell">
            <x-storefront.section-header
                :eyebrow="$homepageSections['featured_categories']->subtitle ?? 'Featured categories'"
                :title="$homepageSections['featured_categories']->title ?? 'Browse the main Azraq Bridal catalog.'"
                :description="$homepageSections['featured_categories']->content ?? 'Handpicked categories curated for your ceremony, gifting, and bridal needs.'"
            />

            <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($featuredCategories as $category)
                    <x-storefront.category-tile :category="$category" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── SIGNATURE NIKAH SPOTLIGHT ────────────────────────────── --}}
    @if ($signatureNikah)
        <section class="section-shell">
            <div class="container-shell">
                <div class="glass-card-brand grid gap-8 p-8 lg:grid-cols-[0.95fr_1.05fr] lg:p-10">
                    <div class="overflow-hidden rounded-[var(--radius-3xl)] bg-[var(--bg-section-soft)]">
                        @php($nikahImage = $signatureNikah->storefront_preview_image_url)
                        @if ($nikahImage)
                            <img src="{{ $nikahImage }}" alt="{{ $signatureNikah->name }}" class="h-full min-h-[430px] w-full object-cover">
                        @endif
                    </div>
                    <div class="flex flex-col justify-center">
                        <span class="section-kicker text-[0.68rem]">Signature Nikah highlight</span>
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
                                <div class="glass-panel p-4 text-sm leading-7 text-[var(--text-main)]">{{ $step }}</div>
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

    {{-- ── FEATURED PRODUCTS ────────────────────────────────────── --}}
    <section id="architecture" class="section-shell">
        <div class="container-shell">
            <x-storefront.section-header
                :eyebrow="$homepageSections['featured_products']->subtitle ?? 'Featured products'"
                :title="$homepageSections['featured_products']->title ?? 'Curated pieces from across the Azraq Bridal catalog.'"
                :description="$homepageSections['featured_products']->content ?? 'Standard, personalized, bundle, and service products — discoverable at a glance.'"
            />

            <div class="mt-10 grid gap-6 lg:grid-cols-2 xl:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-storefront.listing-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── COLLECTIONS ──────────────────────────────────────────── --}}
    <section id="collections" class="section-shell">
        <div class="container-shell">
            <div class="glass-panel grid gap-8 p-8 lg:grid-cols-[1fr_0.9fr] lg:p-10">
                <div>
                    <span class="section-kicker text-[0.68rem]">Featured collections</span>
                    <h2 class="mt-4 text-3xl font-semibold text-[var(--text-main)]">{{ $homepageSections['featured_collections']->title ?? 'Collections curated for ceremony, gifting, and bridal wear.' }}</h2>
                    <p class="mt-4 text-base leading-8 text-[var(--text-muted)]">
                        {{ $homepageSections['featured_collections']->content ?? 'Best-sellers, combo edits, personalized gift picks, and curated merchandising — all in one place.' }}
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

    {{-- ── COMBO SPOTLIGHT ──────────────────────────────────────── --}}
    @if ($comboSpotlight->isNotEmpty())
        <section class="section-shell">
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

    {{-- ── BRIDAL WEAR + BOOKING SERVICES ──────────────────────── --}}
    @if ($bridalWearSpotlight->isNotEmpty() || $bookingHighlights->isNotEmpty())
        <section class="section-shell">
            <div class="container-shell grid gap-8 xl:grid-cols-[1.05fr_0.95fr]">
                @if ($bridalWearSpotlight->isNotEmpty())
                    <div class="glass-panel p-8 lg:p-10">
                        <span class="section-kicker text-[0.68rem]">Bridal wear spotlight</span>
                        <h2 class="mt-4 text-3xl font-semibold text-[var(--text-main)]">Customized bridal wear with soft editorial presentation.</h2>
                        <div class="mt-8 grid gap-5">
                            @foreach ($bridalWearSpotlight as $product)
                                <x-storefront.listing-card :product="$product" />
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($bookingHighlights->isNotEmpty())
                    <div class="glass-card-brand p-8 lg:p-10">
                        <span class="section-kicker text-[0.68rem]">Booking / mehendi services</span>
                        <h2 class="mt-4 text-3xl font-semibold text-[var(--text-main)]">Service-led experiences deserve a gentler, inquiry-first storefront presence.</h2>
                        <p class="mt-4 text-base leading-8 text-[var(--text-muted)]">Bridal, non-bridal, and mehendi bookings are highlighted separately so they do not feel like stock-first products.</p>
                        <div class="mt-8 grid gap-4">
                            @foreach ($bookingHighlights as $service)
                                @php($serviceImage = $service->storefront_preview_image_url)
                                <a href="{{ route('products.show', $service) }}" class="glass-panel block overflow-hidden p-0 transition hover:-translate-y-1 hover:shadow-[var(--shadow-card-hover)]">
                                    <div class="grid gap-0 md:grid-cols-[190px_1fr]">
                                        <div class="overflow-hidden rounded-l-[var(--radius-2xl)] bg-[var(--bg-section-soft)]">
                                            @if ($serviceImage)
                                                <img src="{{ $serviceImage }}" alt="{{ $service->name }}" class="h-full min-h-[200px] w-full object-cover">
                                            @else
                                                <div class="h-full min-h-[200px] w-full bg-[radial-gradient(circle_at_top,_rgba(120,0,0,0.08),_transparent_60%),linear-gradient(180deg,rgba(255,255,255,0.9),rgba(244,237,228,0.84))]"></div>
                                            @endif
                                        </div>
                                        <div class="p-5">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <p class="section-kicker text-[0.6rem]">{{ $service->type?->label() }}</p>
                                                    <h3 class="mt-2 text-2xl font-semibold text-[var(--text-main)]">{{ $service->name }}</h3>
                                                    <p class="mt-2 text-sm leading-7 text-[var(--text-muted)]">{{ \Illuminate\Support\Str::limit($service->excerpt ?: strip_tags($service->description), 120) }}</p>
                                                </div>
                                                <span class="button-pill flex-shrink-0">Inquire</span>
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

    {{-- ── TESTIMONIALS ─────────────────────────────────────────── --}}
    @if ($testimonials->isNotEmpty())
        <section class="section-shell">
            <div class="container-shell">
                <x-storefront.section-header
                    eyebrow="Testimonials"
                    title="Moments shared by brides and families who trusted Azraq."
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

    {{-- ── FAQ PREVIEW ──────────────────────────────────────────── --}}
    @if ($faqPreview->isNotEmpty() && ($homepageSections['faq_preview']->is_enabled ?? true))
        <section class="section-shell">
            <div class="container-shell">
                <x-storefront.section-header
                    :eyebrow="$homepageSections['faq_preview']->subtitle ?? 'FAQ preview'"
                    :title="$homepageSections['faq_preview']->title ?? 'Frequently asked questions'"
                    :description="$homepageSections['faq_preview']->content ?? 'Delivery, personalization, and proof expectations — answered clearly.'"
                />

                <div class="mt-10 grid gap-4">
                    @foreach ($faqPreview as $faq)
                        <article class="glass-panel p-6">
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

    {{-- ── PREMIUM CTA ──────────────────────────────────────────── --}}
    <section class="section-shell">
        <div class="container-shell">
            <div class="glass-card-brand overflow-hidden p-10 text-center lg:p-16">
                <span class="section-kicker text-[0.68rem]">Azraq Bridal</span>
                <h2 class="mt-5 text-4xl font-semibold text-[var(--text-main)] sm:text-5xl">Crafted for moments that last forever</h2>
                <p class="mx-auto mt-5 max-w-xl text-lg leading-8 text-[var(--text-muted)]">From personalized Nikah Namas to full bridal wear and ceremony gifting — every detail is handled with care and presented beautifully.</p>
                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <a href="{{ route('shop.index') }}" class="button-primary">Browse the full collection</a>
                    <a href="{{ route('shop.index', ['type' => 'service']) }}" class="button-ghost">Book a consultation</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.storefront>
