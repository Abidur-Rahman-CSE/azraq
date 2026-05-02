@php
    use App\Models\Product;

    // Detect dev/engineering placeholder strings so admin-leftover copy never reaches the storefront.
    $isDevCopy = function (?string $value): bool {
        if (! filled($value)) return true;
        $needles = ['storefront', 'configurable homepage', 'top-of-funnel', 'hardcoding', 'Phase 1', 'catalog architecture'];
        foreach ($needles as $needle) {
            if (stripos($value, $needle) !== false) return true;
        }
        return false;
    };
    $copy = fn ($value, $fallback) => $isDevCopy($value) ? $fallback : $value;

    // ── Sections (admin-driven, all enabled-only)
    $heroSection      = $homepageSections->get('hero');
    $statsSection     = $homepageSections->get('stats_strip');
    $categoriesSec    = $homepageSections->get('featured_categories');
    $spotlightSection = $homepageSections->get('signature_nikah_spotlight');
    $productsSection  = $homepageSections->get('featured_products');
    $collectionsSec   = $homepageSections->get('featured_collections');
    $atelierSection   = $homepageSections->get('atelier_services');
    $finaleSection    = $homepageSections->get('finale_cta');
    $instaSection     = $homepageSections->get('instagram_strip');
    $trustSection     = $homepageSections->get('trust_strip');
    $faqSection       = $homepageSections->get('faq_preview');

    // ── Hero composition
    $heroImage = data_get($heroSection, 'settings.desktop_image_url')
        ?: $signatureNikah?->storefront_preview_image_url
        ?: $featuredProducts->first()?->storefront_preview_image_url;
    $heroKicker = $copy($heroSection?->subtitle, 'Bridal Atelier · Dhaka');
    $heroTitle  = $copy($heroSection?->title, 'Crafted for the moment that lasts forever.');
    $heroBody   = $copy($heroSection?->content, 'Premium Nikah personalization, bridal wear, and ceremony gifting in one curated atelier.');
    $heroCta    = filled($heroSection?->cta_label) ? $heroSection->cta_label : 'Configure your Nikah';
    $heroHref   = filled($heroSection?->cta_href) ? $heroSection->cta_href : ($signatureNikah ? route('products.show', $signatureNikah) : route('shop.index'));
    $heroSecLabel = data_get($heroSection, 'settings.secondary_cta_label') ?: 'See curated editions ↓';
    $heroSecHref  = data_get($heroSection, 'settings.secondary_cta_href') ?: '#curated';

    $heroChipProductId = (int) data_get($heroSection, 'settings.featured_product_id');
    $heroChip = $heroChipProductId > 0
        ? Product::with('category')->find($heroChipProductId)
        : $signatureNikah;

    // ── Stats
    $stats = collect(data_get($statsSection, 'settings.stats', []))
        ->filter(fn ($s) => filled(data_get($s, 'num')) || filled(data_get($s, 'label')));

    // ── Curated editions: merge sources, dedupe, pick 4 or 8
    $curatedPool = collect()
        ->merge($featuredProducts ?? collect())
        ->merge($comboSpotlight ?? collect())
        ->merge($bridalWearSpotlight ?? collect())
        ->unique('id');
    $curatedEditions = $curatedPool->take($curatedPool->count() >= 8 ? 8 : 4);

    // ── Testimonial
    $featuredTestimonial = $testimonials->sortByDesc('rating')->sortByDesc(fn ($r) => mb_strlen($r->body ?? ''))->first();
    $supportingTestimonials = $testimonials->reject(fn ($r) => $featuredTestimonial && $r->id === $featuredTestimonial->id)->take(2);

    // ── Process steps (Signature Nikah)
    $processSteps = collect(data_get($spotlightSection, 'settings.process_steps', [
        '01 Fill details', '02 Choose typography', '03 Approve proof',
    ]))->filter()->take(6);

    // ── Finale
    $finaleBg = data_get($finaleSection, 'settings.background_image_url')
        ?: $signatureNikah?->storefront_preview_image_url
        ?: $bridalWearSpotlight->first()?->storefront_preview_image_url;
@endphp

<x-layouts.storefront
    title="Azraq Bridal | Storefront"
    canonical="{{ route('home') }}"
    :social-image="$heroImage"
    :schema-data="[
        ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => config('brand.name'), 'url' => route('home')],
    ]"
>
    {{-- ── 1. EDITORIAL HERO ─────────────────────────────────── --}}
    <section class="section-shell section-shell--tight overflow-hidden scroll-fade-in">
        <div class="container-shell editorial-hero-grid grid items-center gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:gap-12">
            <div class="order-2 lg:order-1 min-w-0">
                <span class="section-kicker text-[0.62rem]">{{ $heroKicker }}</span>
                <h1 class="mt-4 font-serif text-4xl font-semibold leading-[1.05] tracking-[-0.015em] text-[var(--text-main)] sm:text-5xl lg:text-6xl" style="text-wrap: balance; font-family: 'Cormorant Garamond', Georgia, serif;">
                    {{ $heroTitle }}
                </h1>
                <p class="mt-5 max-w-md text-base leading-7 text-[var(--text-muted)] sm:text-lg">
                    {{ $heroBody }}
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="{{ $heroHref }}" class="button-primary">{{ $heroCta }}</a>
                    <a href="{{ $heroSecHref }}" class="text-sm font-semibold tracking-[0.06em] text-[var(--accent-secondary)] hover:text-[var(--accent-primary)]">{{ $heroSecLabel }}</a>
                </div>
            </div>

            <div class="order-1 lg:order-2 relative min-w-0">
                <div class="editorial-hero__visual relative overflow-hidden rounded-[var(--radius-3xl)] aspect-[4/5] sm:aspect-[5/4] lg:aspect-[6/7] bg-[var(--bg-section-soft)]">
                    @if ($heroImage)
                        <img src="{{ $heroImage }}" alt="Azraq Bridal — featured atelier piece" class="editorial-hero__img absolute inset-0 h-full w-full object-cover">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-[rgba(7,14,24,0.55)] via-[rgba(7,14,24,0.10)] to-transparent"></div>

                    <span class="absolute right-4 top-4 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-[0.62rem] font-semibold uppercase tracking-[0.18em] text-white backdrop-blur-md">
                        <span class="text-[var(--azraq-blue)]">◆</span> 12 yrs · 350+ brides
                    </span>

                    @if ($heroChip)
                        <a href="{{ route('products.show', $heroChip) }}" class="absolute bottom-4 left-4 right-4 flex items-center justify-between gap-3 rounded-[var(--radius-xl)] bg-white/12 px-4 py-3 text-white backdrop-blur-md hover:bg-white/20 transition">
                            <span class="min-w-0">
                                <span class="block text-[0.58rem] font-semibold uppercase tracking-[0.2em] text-white/70">Featured · {{ $heroChip->category?->name }}</span>
                                <span class="mt-0.5 block truncate text-sm font-semibold">{{ $heroChip->name }}</span>
                            </span>
                            <span class="flex-shrink-0 text-[0.7rem] font-semibold tracking-[0.12em] uppercase">BDT {{ number_format((float) $heroChip->price, 0) }} →</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ── 2. STATS STRIP ────────────────────────────────────── --}}
    @if ($statsSection && $stats->isNotEmpty())
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
    @endif

    {{-- ── 3. CIRCLE CATEGORY STRIP ─────────────────────────── --}}
    @if ($categoriesSec && $featuredCategories->isNotEmpty())
        <section id="catalog" class="section-shell scroll-fade-in">
            <div class="container-shell">
                <x-storefront.section-header
                    :eyebrow="$categoriesSec->subtitle ?? 'Catalogue'"
                    :title="$copy($categoriesSec->title, 'Shop by category.')"
                    :description="$copy($categoriesSec->content, 'An atelier organised the way you plan a wedding — Nikah, bridal wear, accessories, gifts, and bookings.')"
                    centered
                />

                <div class="mt-8 category-circle-rail">
                    @foreach ($featuredCategories as $category)
                        <x-storefront.category-circle :category="$category" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ── 4. SIGNATURE NIKAH SPOTLIGHT ──────────────────────── --}}
    @if ($signatureNikah && $spotlightSection)
        <section class="section-shell scroll-fade-in">
            <div class="container-shell">
                <div class="glass-card-brand grid gap-6 p-5 sm:p-7 lg:grid-cols-[0.95fr_1.05fr] lg:gap-10 lg:p-10">
                    <div class="overflow-hidden rounded-[var(--radius-3xl)] bg-[var(--bg-section-soft)] aspect-[4/5] sm:aspect-[5/4] lg:aspect-auto lg:min-h-[440px]">
                        @php($nikahImage = $signatureNikah->storefront_preview_image_url)
                        @if ($nikahImage)
                            <img src="{{ $nikahImage }}" alt="{{ $signatureNikah->name }}" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="flex flex-col justify-center min-w-0">
                        <span class="section-kicker text-[0.62rem]">{{ $spotlightSection->subtitle ?? 'Signature Nikah Nama' }}</span>
                        <h2 class="mt-3 text-3xl font-semibold leading-[1.1] tracking-[-0.015em] text-[var(--text-main)] sm:text-4xl lg:text-5xl" style="font-family: 'Cormorant Garamond', Georgia, serif;">{{ $copy($spotlightSection->title, $signatureNikah->name) }}</h2>
                        <p class="mt-4 text-sm leading-7 text-[var(--text-muted)] sm:text-base">{{ \Illuminate\Support\Str::limit($copy($spotlightSection->content, $signatureNikah->description), 220) }}</p>

                        @if ($processSteps->isNotEmpty())
                            <div class="mt-6 process-rail">
                                @foreach ($processSteps as $idx => $step)
                                    <span class="process-rail__step">{{ $step }}</span>
                                    @if (!$loop->last)
                                        <span class="process-rail__sep">·</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-7 flex flex-wrap gap-3">
                            <a href="{{ filled($spotlightSection->cta_href) ? $spotlightSection->cta_href : route('products.show', $signatureNikah) }}" class="button-primary">{{ $spotlightSection->cta_label ?: 'Customize your Nikah' }}</a>
                            @php($secLabel = data_get($spotlightSection, 'settings.secondary_cta_label'))
                            @php($secHref  = data_get($spotlightSection, 'settings.secondary_cta_href'))
                            @if ($secLabel && $secHref)
                                <a href="{{ $secHref }}" class="button-ghost">{{ $secLabel }}</a>
                            @elseif ($signatureNikah->category)
                                <a href="{{ route('categories.show', $signatureNikah->category) }}" class="button-ghost">Explore Nikah Collection</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ── 5. CURATED EDITIONS ───────────────────────────────── --}}
    @if ($productsSection && $curatedEditions->isNotEmpty())
        <section id="curated" class="section-shell scroll-fade-in">
            <div class="container-shell">
                <x-storefront.section-header
                    :eyebrow="$productsSection->subtitle ?? 'Curated editions'"
                    :title="$copy($productsSection->title, 'Pieces we keep returning to.')"
                    :description="$copy($productsSection->content, 'Bridal wear, Nikah essentials, gifting combos, and bookings — curated weekly.')"
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
                    <a href="{{ filled($productsSection->cta_href) ? $productsSection->cta_href : route('shop.index') }}" class="button-ghost">{{ $productsSection->cta_label ?: 'Open the full shop →' }}</a>
                </div>
            </div>
        </section>
    @endif

    {{-- ── 6. FEATURED COLLECTIONS (richer) ───────────────────── --}}
    @if ($collectionsSec && $featuredCollections->isNotEmpty())
        <section id="collections" class="section-shell scroll-fade-in">
            <div class="container-shell">
                <x-storefront.section-header
                    :eyebrow="$collectionsSec->subtitle ?? 'Editions'"
                    :title="$copy($collectionsSec->title, 'Edits we keep returning to.')"
                    :description="$copy($collectionsSec->content, 'Best-sellers, combo edits, personalized gift picks, and curated merchandising — all in one place.')"
                    centered
                />

                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                    @foreach ($featuredCollections as $collection)
                        <x-storefront.collection-card :collection="$collection" />
                    @endforeach
                </div>

                @if (filled($collectionsSec->cta_label) && filled($collectionsSec->cta_href))
                    <div class="mt-10 text-center">
                        <a href="{{ $collectionsSec->cta_href }}" class="button-ghost">{{ $collectionsSec->cta_label }}</a>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ── 7. EDITORIAL TESTIMONIAL ──────────────────────────── --}}
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

    {{-- ── 8. INSTAGRAM STRIP (optional) ─────────────────────── --}}
    @php($instaPosts = collect(data_get($instaSection, 'settings.posts', [])))
    @if ($instaSection && $instaPosts->isNotEmpty())
        <section class="section-shell--tight scroll-fade-in">
            <div class="container-shell">
                <x-storefront.section-header
                    :eyebrow="$instaSection->subtitle ?? 'Instagram'"
                    :title="$copy($instaSection->title, 'From our atelier.')"
                    :description="$instaSection->content"
                    centered
                />

                <div class="mt-7">
                    <x-storefront.instagram-strip :posts="$instaPosts" />
                </div>

                @if (filled($instaSection->cta_label) && filled($instaSection->cta_href))
                    <div class="mt-6 text-center">
                        <a href="{{ $instaSection->cta_href }}" class="text-sm font-semibold text-[var(--accent-primary)] hover:underline" target="_blank" rel="noopener noreferrer">{{ $instaSection->cta_label }} →</a>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ── 9. ATELIER SERVICES ───────────────────────────────── --}}
    @if ($atelierSection && $bookingHighlights->isNotEmpty())
        <section class="section-shell scroll-fade-in">
            <div class="container-shell">
                <x-storefront.section-header
                    :eyebrow="$atelierSection->subtitle ?? 'Atelier services'"
                    :title="$copy($atelierSection->title, 'Bookings handled with the same care as our pieces.')"
                    :description="$copy($atelierSection->content, 'Bridal makeup, mehendi, and ceremony consultations — inquiry-first, never stock-first.')"
                    centered
                />

                <div class="mt-8">
                    <x-storefront.carousel :md-cols="3" :lg-cols="3">
                        @foreach ($bookingHighlights->take(3) as $service)
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

    {{-- ── 10. CINEMATIC CTA FINALE ──────────────────────────── --}}
    @if ($finaleSection)
        <section class="section-shell scroll-fade-in">
            <div class="container-shell">
                <div class="cta-finale">
                    @if ($finaleBg)
                        <img src="{{ $finaleBg }}" alt="" class="cta-finale__bg" loading="lazy">
                    @else
                        <div class="cta-finale__fallback"></div>
                    @endif
                    <div class="cta-finale__overlay"></div>
                    <div class="cta-finale__content">
                        <span class="section-kicker text-[0.62rem] text-white/70">{{ $finaleSection->subtitle ?? 'Begin the journey' }}</span>
                        <h2 class="cta-finale__title mt-3" style="color:#fff;">{{ $copy($finaleSection->title, 'Crafted for moments that last forever.') }}</h2>
                        <p class="cta-finale__sub">{{ $copy($finaleSection->content, 'A 15-minute consultation. Zero obligation. Visit our atelier or chat on WhatsApp.') }}</p>
                        <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ $finaleSection->cta_href ?: route('shop.index', ['type' => 'service']) }}" class="button-luxury">{{ $finaleSection->cta_label ?: 'Book a consultation' }}</a>
                            @php($secLabel = data_get($finaleSection, 'settings.secondary_cta_label'))
                            @php($secHref  = data_get($finaleSection, 'settings.secondary_cta_href'))
                            @if ($secLabel && $secHref)
                                <a href="{{ $secHref }}" class="button-outline-gold">{{ $secLabel }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ── 11. TRUST STRIP ───────────────────────────────────── --}}
    @php($trustSignals = collect(data_get($trustSection, 'settings.signals', [])))
    @if ($trustSection && $trustSignals->isNotEmpty())
        <section class="section-shell--tight scroll-fade-in">
            <div class="container-shell">
                <x-storefront.trust-strip :signals="$trustSignals" />
            </div>
        </section>
    @endif

    {{-- ── 12. FAQ PRELUDE ───────────────────────────────────── --}}
    @if ($faqSection && $faqPreview->isNotEmpty())
        <section class="section-shell--tight pb-12 text-center scroll-fade-in">
            <p class="text-sm text-[var(--text-muted)]">
                Have a question?
                <a href="{{ filled($faqSection->cta_href) ? $faqSection->cta_href : route('faq.index') }}" class="ml-1 font-semibold text-[var(--accent-primary)] hover:underline">{{ $faqSection->cta_label ?: 'See FAQs →' }}</a>
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
