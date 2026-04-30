@php
    // Detect dev/engineering placeholder strings so admin-leftover copy
    // never reaches the storefront. Match common giveaways from the seeded data.
    $isDevCopy = function (?string $value): bool {
        if (! filled($value)) return true;
        $needles = ['storefront', 'configurable homepage', 'top-of-funnel', 'hardcoding', 'Phase 1', 'catalog architecture'];
        foreach ($needles as $needle) {
            if (stripos($value, $needle) !== false) return true;
        }
        return false;
    };

    $heroSection = $homepageSections->get('hero');
    $heroImage = data_get($heroSection, 'settings.desktop_image_url')
        ?: $signatureNikah?->storefront_preview_image_url
        ?: $featuredProducts->first()?->storefront_preview_image_url;

    $heroKicker = $isDevCopy($heroSection->subtitle ?? null) ? 'Bridal Atelier · Dhaka' : $heroSection->subtitle;
    $heroTitle  = $isDevCopy($heroSection->title ?? null) ? 'Crafted for the moment that lasts forever.' : $heroSection->title;
    $heroBody   = $isDevCopy($heroSection->content ?? null) ? 'Premium Nikah personalization, bridal wear, and ceremony gifting in one curated atelier.' : $heroSection->content;
    $heroCta    = filled($heroSection?->cta_label ?? null) ? $heroSection->cta_label : 'Configure your Nikah';
    $heroHref   = filled($heroSection?->cta_href ?? null) ? $heroSection->cta_href : ($signatureNikah ? route('products.show', $signatureNikah) : route('shop.index'));

    // Bento mosaic: hero (8col × 2row) + exactly 2 cells (4col each) on the right.
    // Extras row (below) only renders when 2 leftover categories exist — never 1, to avoid orphan.
    $bentoHero = $featuredCategories->first();
    $bentoSidecells = $featuredCategories->skip(1)->take(2);
    $remaining = $featuredCategories->skip(3);
    $bentoExtras = $remaining->count() >= 2 ? $remaining->take(2) : collect();

    // Curated editions: merge sources, dedupe, then pick a count that
    // fills exactly one or two desktop rows (4 or 8) — never orphans.
    $curatedPool = collect()
        ->merge($featuredProducts ?? collect())
        ->merge($comboSpotlight ?? collect())
        ->merge($bridalWearSpotlight ?? collect())
        ->unique('id');

    $curatedCount = $curatedPool->count() >= 8 ? 8 : 4;
    $curatedEditions = $curatedPool->take($curatedCount);

    // Stats — fall back to brand defaults if no admin override
    $stats = [
        ['num' => '350+', 'label' => 'Brides served'],
        ['num' => '48 hr', 'label' => 'Proof turnaround'],
        ['num' => '12 yrs', 'label' => 'Atelier in Dhaka'],
        ['num' => '100%', 'label' => 'Hand finished'],
    ];

    // Featured testimonial: highest rating, longest body
    $featuredTestimonial = $testimonials->sortByDesc('rating')->sortByDesc(fn ($r) => mb_strlen($r->body ?? ''))->first();
    $supportingTestimonials = $testimonials->reject(fn ($r) => $featuredTestimonial && $r->id === $featuredTestimonial->id)->take(2);
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
    {{-- ── 1. EDITORIAL HERO ──────────────────────────────────── --}}
    <section class="section-shell section-shell--tight overflow-hidden scroll-fade-in">
        <div class="container-shell grid items-center gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:gap-12">
            <div class="order-2 lg:order-1">
                <span class="section-kicker text-[0.62rem]">{{ $heroKicker }}</span>
                <h1 class="mt-4 text-3xl font-semibold leading-[1.1] tracking-[-0.02em] text-[var(--text-main)] sm:text-4xl lg:text-6xl" style="text-wrap: balance;">
                    {{ $heroTitle }}
                </h1>
                <p class="mt-5 max-w-md text-base leading-7 text-[var(--text-muted)] sm:text-lg">
                    {{ $heroBody }}
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="{{ $heroHref }}" class="button-primary">{{ $heroCta }}</a>
                    <a href="#curated" class="text-sm font-semibold tracking-[0.06em] text-[var(--accent-secondary)] hover:text-[var(--accent-primary)]">See curated editions ↓</a>
                </div>
            </div>

            <div class="order-1 lg:order-2 relative">
                <div class="relative overflow-hidden rounded-[var(--radius-3xl)] aspect-[4/5] sm:aspect-[5/4] lg:aspect-[4/5] bg-[var(--bg-section-soft)]">
                    @if ($heroImage)
                        <img src="{{ $heroImage }}" alt="Azraq Bridal — featured atelier piece" class="editorial-hero__img absolute inset-0 h-full w-full object-cover">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-[rgba(7,14,24,0.55)] via-[rgba(7,14,24,0.10)] to-transparent"></div>

                    <span class="absolute right-4 top-4 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-[0.62rem] font-semibold uppercase tracking-[0.18em] text-white backdrop-blur-md">
                        <span class="text-[var(--azraq-blue)]">◆</span> 12 yrs · 350+ brides
                    </span>

                    @if ($signatureNikah)
                        <a href="{{ route('products.show', $signatureNikah) }}" class="absolute bottom-4 left-4 right-4 flex items-center justify-between gap-3 rounded-[var(--radius-xl)] bg-white/12 px-4 py-3 text-white backdrop-blur-md hover:bg-white/20 transition">
                            <span class="min-w-0">
                                <span class="block text-[0.58rem] font-semibold uppercase tracking-[0.2em] text-white/70">Featured · {{ $signatureNikah->category?->name }}</span>
                                <span class="mt-0.5 block truncate text-sm font-semibold">{{ $signatureNikah->name }}</span>
                            </span>
                            <span class="flex-shrink-0 text-[0.7rem] font-semibold tracking-[0.12em] uppercase">BDT {{ number_format((float) $signatureNikah->price, 0) }} →</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ── 2. STATS STRIP ─────────────────────────────────────── --}}
    <section class="section-shell--tight px-4 sm:px-6 scroll-fade-in">
        <div class="container-shell">
            <div class="stats-strip">
                @foreach ($stats as $stat)
                    <div class="stats-cell">
                        <p class="stats-cell__num">{{ $stat['num'] }}</p>
                        <p class="stats-cell__label">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── 3. BENTO CATEGORY MOSAIC ──────────────────────────── --}}
    @if ($featuredCategories->isNotEmpty())
        <section id="catalog" class="section-shell scroll-fade-in">
            <div class="container-shell">
                <x-storefront.section-header
                    eyebrow="Catalogue"
                    title="An atelier organised the way you plan a wedding."
                    description="Handpicked categories — Nikah, bridal wear, accessories, combos, and bookings — all in one place."
                />

                <div class="mt-10 bento-mosaic">
                    @if ($bentoHero)
                        <div class="bento-hero">
                            <x-storefront.category-tile :category="$bentoHero" variant="hero" />
                        </div>
                    @endif
                    @foreach ($bentoSidecells as $cat)
                        <div class="bento-cell">
                            <x-storefront.category-tile :category="$cat" />
                        </div>
                    @endforeach
                    @foreach ($bentoExtras as $cat)
                        <div class="bento-cell bento-cell--half">
                            <x-storefront.category-tile :category="$cat" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ── 4. SIGNATURE NIKAH SPOTLIGHT ──────────────────────── --}}
    @if ($signatureNikah)
        <section class="section-shell scroll-fade-in">
            <div class="container-shell">
                <div class="glass-card-brand grid gap-6 p-5 sm:p-7 lg:grid-cols-[0.95fr_1.05fr] lg:gap-10 lg:p-10">
                    <div class="overflow-hidden rounded-[var(--radius-3xl)] bg-[var(--bg-section-soft)] aspect-[4/5] sm:aspect-[5/4] lg:aspect-auto lg:min-h-[440px]">
                        @php($nikahImage = $signatureNikah->storefront_preview_image_url)
                        @if ($nikahImage)
                            <img src="{{ $nikahImage }}" alt="{{ $signatureNikah->name }}" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="flex flex-col justify-center">
                        <span class="section-kicker text-[0.62rem]">Signature Nikah Nama</span>
                        <h2 class="mt-3 text-2xl font-semibold leading-tight text-[var(--text-main)] sm:text-3xl lg:text-4xl">{{ $signatureNikah->name }}</h2>
                        <p class="mt-4 text-sm leading-7 text-[var(--text-muted)] sm:text-base">{{ \Illuminate\Support\Str::limit($signatureNikah->description, 220) }}</p>

                        <div class="mt-6 process-rail">
                            <span class="process-rail__step"><span class="process-rail__num">01</span> Fill details</span>
                            <span class="process-rail__sep">·</span>
                            <span class="process-rail__step"><span class="process-rail__num">02</span> Choose typography</span>
                            <span class="process-rail__sep">·</span>
                            <span class="process-rail__step"><span class="process-rail__num">03</span> Approve proof</span>
                        </div>

                        <div class="mt-7 flex flex-wrap gap-3">
                            <a href="{{ route('products.show', $signatureNikah) }}" class="button-primary">Customize your Nikah</a>
                            @if ($signatureNikah->category)
                                <a href="{{ route('categories.show', $signatureNikah->category) }}" class="button-ghost">Explore Nikah Collection</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ── 5. CURATED EDITIONS (carousel/grid) ───────────────── --}}
    @if ($curatedEditions->isNotEmpty())
        <section id="curated" class="section-shell scroll-fade-in">
            <div class="container-shell">
                <x-storefront.section-header
                    eyebrow="Curated editions"
                    title="Pieces we keep returning to."
                    description="Bridal wear, Nikah essentials, gifting combos, and bookings — curated weekly."
                    centered
                />

                <div class="mt-7 filter-pill-row">
                    <a href="{{ route('shop.index') }}" class="filter-pill filter-pill--active">All</a>
                    <a href="{{ route('shop.index', ['type' => 'nikah_personalization']) }}" class="filter-pill">Nikah</a>
                    <a href="{{ route('shop.index', ['type' => 'advanced_personalization']) }}" class="filter-pill">Bridal wear</a>
                    <a href="{{ route('shop.index', ['type' => 'bundle']) }}" class="filter-pill">Combos</a>
                    <a href="{{ route('shop.index', ['type' => 'service']) }}" class="filter-pill">Bookings</a>
                </div>

                <div class="mt-8">
                    <x-storefront.carousel :md-cols="3" :lg-cols="4">
                        @foreach ($curatedEditions as $product)
                            <x-storefront.listing-card :product="$product" />
                        @endforeach
                    </x-storefront.carousel>
                </div>

                <div class="mt-10 text-center">
                    <a href="{{ route('shop.index') }}" class="button-ghost">Open the full shop →</a>
                </div>
            </div>
        </section>
    @endif

    {{-- ── 6. EDITORIAL TESTIMONIAL PULL-QUOTE ───────────────── --}}
    @if ($featuredTestimonial)
        <section class="section-shell scroll-fade-in">
            <div class="container-shell">
                <article class="editorial-quote mx-auto max-w-3xl text-center">
                    <p class="editorial-quote__body">"{{ \Illuminate\Support\Str::limit($featuredTestimonial->body, 220) }}"</p>
                    <div class="editorial-quote__attribution justify-center">
                        <p class="text-sm font-semibold text-[var(--accent-secondary)]">
                            {{ $featuredTestimonial->author_name }}
                            @if ($featuredTestimonial->title)
                                <span class="ml-2 text-[var(--text-muted)] font-normal">· {{ $featuredTestimonial->title }}</span>
                            @endif
                        </p>
                        <p class="text-sm tracking-wide text-[var(--azraq-burgundy)]">
                            {{ str_repeat('★', $featuredTestimonial->rating) }}<span class="text-[var(--text-muted)] opacity-30">{{ str_repeat('★', max(0, 5 - $featuredTestimonial->rating)) }}</span>
                        </p>
                    </div>
                </article>

                @if ($supportingTestimonials->isNotEmpty())
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach ($supportingTestimonials as $review)
                            <x-storefront.review-card :review="$review" />
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ── 7. ATELIER SERVICES ───────────────────────────────── --}}
    @if ($bookingHighlights->isNotEmpty())
        <section class="section-shell scroll-fade-in">
            <div class="container-shell">
                <x-storefront.section-header
                    eyebrow="Atelier services"
                    title="Bookings handled with the same care as our pieces."
                    description="Bridal makeup, mehendi, and ceremony consultations — inquiry-first, never stock-first."
                    centered
                />

                <div class="mt-8">
                    <x-storefront.carousel :md-cols="3" :lg-cols="3">
                        @foreach ($bookingHighlights as $service)
                            @php($serviceImage = $service->storefront_preview_image_url)
                            <a href="{{ route('products.show', $service) }}" class="glass-panel group block overflow-hidden">
                                <div class="overflow-hidden rounded-t-[var(--radius-2xl)] aspect-[4/3]">
                                    @if ($serviceImage)
                                        <img src="{{ $serviceImage }}" alt="{{ $service->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    @else
                                        <div class="h-full w-full bg-[radial-gradient(circle_at_top,_rgba(120,0,0,0.10),_transparent_60%),linear-gradient(180deg,rgba(255,255,255,0.92),rgba(244,237,228,0.84))]"></div>
                                    @endif
                                </div>
                                <div class="p-4 sm:p-5">
                                    <p class="section-kicker text-[0.6rem]">{{ $service->type?->label() }}</p>
                                    <h3 class="mt-2 text-base font-semibold leading-tight text-[var(--text-main)] sm:text-lg">{{ $service->name }}</h3>
                                    <p class="mt-2 hidden sm:block text-xs leading-6 text-[var(--text-muted)]">{{ \Illuminate\Support\Str::limit($service->excerpt ?: strip_tags($service->description), 90) }}</p>
                                    <div class="mt-3 flex items-center justify-between">
                                        <span class="text-sm font-semibold text-[var(--text-main)]">BDT {{ number_format((float) $service->price, 0) }}</span>
                                        <span class="product-card-lux__cta">Inquire →</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </x-storefront.carousel>
                </div>
            </div>
        </section>
    @endif

    {{-- ── 8. CINEMATIC CTA FINALE ───────────────────────────── --}}
    @php($finaleImage = $signatureNikah?->storefront_preview_image_url ?: $bridalWearSpotlight->first()?->storefront_preview_image_url)
    <section class="section-shell scroll-fade-in">
        <div class="container-shell">
            <div class="cta-finale">
                @if ($finaleImage)
                    <img src="{{ $finaleImage }}" alt="" class="cta-finale__bg" loading="lazy">
                @else
                    <div class="cta-finale__fallback"></div>
                @endif
                <div class="cta-finale__overlay"></div>
                <div class="cta-finale__content">
                    <span class="section-kicker text-[0.62rem] text-white/70">Begin the journey</span>
                    <h2 class="cta-finale__title mt-3" style="color:#fff;">Crafted for moments that last forever.</h2>
                    <p class="cta-finale__sub">A 15-minute consultation. Zero obligation. Visit our atelier or chat on WhatsApp.</p>
                    <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('shop.index', ['type' => 'service']) }}" class="button-luxury">Book a consultation</a>
                        <a href="{{ route('shop.index') }}" class="button-outline-gold">Browse the shop</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── 9. FAQ PRELUDE LINE ───────────────────────────────── --}}
    @if ($faqPreview->isNotEmpty() && ($homepageSections['faq_preview']->is_enabled ?? true))
        <section class="section-shell--tight pb-12 text-center scroll-fade-in">
            <p class="text-sm text-[var(--text-muted)]">
                Have a question?
                <a href="{{ route('faq.index') }}" class="ml-1 font-semibold text-[var(--accent-primary)] hover:underline">See FAQs →</a>
            </p>
        </section>
    @endif

    <script>
        (function () {
            if (typeof window === 'undefined' || !('IntersectionObserver' in window)) return;
            document.documentElement.classList.add('js-fade-ready');
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.querySelectorAll('.scroll-fade-in').forEach(el => el.classList.add('is-visible'));
                return;
            }
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
            document.querySelectorAll('.scroll-fade-in').forEach(el => observer.observe(el));
        })();
    </script>
</x-layouts.storefront>
