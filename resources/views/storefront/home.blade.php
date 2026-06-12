@php
    use App\Models\Product;
    use App\Support\MockupZoneNormalizer;

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
    $mockupLayerFor = function (?Product $product): ?array {
        if (! $product?->is_customizable) {
            return null;
        }

        $defaultMockup = $product->defaultPersonalizationMockup();
        $template = $product->relationLoaded('personalizationTemplate')
            ? $product->personalizationTemplate
            : $product->personalizationTemplate()->first();
        $map = $defaultMockup?->map
            ? MockupZoneNormalizer::toImageSpace($defaultMockup, $defaultMockup->map)
            : null;
        $flatArtwork = $template?->thumbnailArtworkUrl()
            ?: $template?->previewArtworkUrl()
            ?: $template?->baseArtworkUrl();

        if (! $defaultMockup?->base_image_url || ! $flatArtwork || ! is_array($map)) {
            return null;
        }

        return [
            'mockup' => $defaultMockup,
            'template' => $template,
            'map' => $map,
            'flatArtwork' => $flatArtwork,
        ];
    };

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

    // ── Hero carousel slides (admin-driven via settings.slides[])
    $defaultHeroImage = data_get($heroSection, 'settings.desktop_image_url')
        ?: $signatureNikah?->storefront_preview_image_url
        ?: $featuredProducts->first()?->storefront_preview_image_url;

    $defaultSlide = [
        'image_url'   => $defaultHeroImage,
        'title'       => $copy($heroSection?->title, 'Crafted for the moment that lasts forever.'),
        'subtitle'    => $copy($heroSection?->subtitle, 'Bridal Atelier · Dhaka'),
        'body'        => $copy($heroSection?->content, 'Premium Nikah personalization, bridal wear, and ceremony gifting in one curated atelier.'),
        'cta_label'   => filled($heroSection?->cta_label) ? $heroSection->cta_label : 'Configure your Nikah',
        'cta_href'    => filled($heroSection?->cta_href) ? $heroSection->cta_href : ($signatureNikah ? route('products.show', $signatureNikah) : route('shop.index')),
        'cta2_label'  => data_get($heroSection, 'settings.secondary_cta_label') ?: '',
        'cta2_href'   => data_get($heroSection, 'settings.secondary_cta_href') ?: '',
    ];

    $heroSlides = collect(data_get($heroSection, 'settings.slides', []))
        ->filter(fn ($s) => filled(data_get($s, 'title')) || filled(data_get($s, 'image_url')))
        ->map(fn ($s) => [
            'image_url'  => $s['image_url'] ?? $defaultHeroImage,
            'title'      => $copy($s['title'] ?? null, $defaultSlide['title']),
            'subtitle'   => $s['subtitle'] ?? $defaultSlide['subtitle'],
            'body'       => $s['body'] ?? $defaultSlide['body'],
            'cta_label'  => $s['cta_label'] ?? $defaultSlide['cta_label'],
            'cta_href'   => $s['cta_href'] ?? $defaultSlide['cta_href'],
            'cta2_label' => $s['cta2_label'] ?? null,
            'cta2_href'  => $s['cta2_href'] ?? null,
        ]);

    if ($heroSlides->isEmpty()) {
        $heroSlides = collect([$defaultSlide]);
    }

    $heroImage = $heroSlides->first()['image_url'] ?? $defaultHeroImage;

    // ── Stats
    $stats = collect(data_get($statsSection, 'settings.stats', []))
        ->filter(fn ($s) => filled(data_get($s, 'num')) || filled(data_get($s, 'label')));

    // ── Curated editions: merge sources, dedupe, pick 4 or 8
    $curatedPool = collect()
        ->merge($featuredProducts ?? collect())
        ->merge($comboSpotlight ?? collect())
        ->merge($bridalWearSpotlight ?? collect())
        ->merge($bookingHighlights ?? collect())
        ->unique('id');
    $curatedEditions = $curatedPool->take($curatedPool->count() >= 12 ? 12 : 8);
    $curatedFilterKeys = function (Product $product): array {
        $keys = [];

        if ($product->type === \App\Enums\ProductType::AdvancedPersonalized || $product->category?->slug === 'nikah-collection') {
            $keys[] = 'nikah';
        }

        if ($product->category?->slug === 'customized-bridal-wear') {
            $keys[] = 'bridal';
        }

        if ($product->type === \App\Enums\ProductType::Bundle) {
            $keys[] = 'combos';
        }

        if ($product->type === \App\Enums\ProductType::Service) {
            $keys[] = 'bookings';
        }

        return array_values(array_unique($keys));
    };
    $curatedFilters = collect([
        ['key' => 'all', 'label' => 'All', 'count' => $curatedEditions->count()],
        ['key' => 'nikah', 'label' => 'Nikah', 'count' => $curatedEditions->filter(fn (Product $product) => in_array('nikah', $curatedFilterKeys($product), true))->count()],
        ['key' => 'bridal', 'label' => 'Bridal wear', 'count' => $curatedEditions->filter(fn (Product $product) => in_array('bridal', $curatedFilterKeys($product), true))->count()],
        ['key' => 'combos', 'label' => 'Combos', 'count' => $curatedEditions->filter(fn (Product $product) => in_array('combos', $curatedFilterKeys($product), true))->count()],
        ['key' => 'bookings', 'label' => 'Bookings', 'count' => $curatedEditions->filter(fn (Product $product) => in_array('bookings', $curatedFilterKeys($product), true))->count()],
    ])->filter(fn ($filter) => $filter['key'] === 'all' || $filter['count'] > 0)->values();

    // ── Testimonial
    $featuredTestimonial = $testimonials->sortByDesc('rating')->sortByDesc(fn ($r) => mb_strlen($r->body ?? ''))->first();
    $supportingTestimonials = $testimonials->reject(fn ($r) => $featuredTestimonial && $r->id === $featuredTestimonial->id)->take(2);

    // ── Process steps (Signature Nikah)
    $processSteps = collect(data_get($spotlightSection, 'settings.process_steps', [
        '01 Fill details', '02 Choose typography', '03 Approve proof',
    ]))->filter()->take(6);
    $signatureNikahHref = $signatureNikah ? route('products.show', $signatureNikah) : route('shop.index');

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
    {{-- ── 1. PREMIUM FULL-BLEED HERO CAROUSEL ─────────────────── --}}
    @php($heroSlidesJson = json_encode($heroSlides->values()->all()))
    <div
        x-data="heroCarousel({{ $heroSlidesJson }})"
        x-init="init()"
        @mouseenter="pause()"
        @mouseleave="resume()"
        class="hero-carousel scroll-fade-in"
        role="region"
        aria-label="Hero"
    >
        @foreach ($heroSlides as $idx => $slide)
            <div
                class="hero-slide {{ $idx === 0 ? 'is-active' : '' }}"
                :class="current === {{ $idx }} ? 'is-active' : ''"
            >
                @if (!empty($slide['image_url']))
                    <img src="{{ $slide['image_url'] }}" alt="{{ $slide['title'] }}" class="hero-slide__bg" loading="{{ $idx === 0 ? 'eager' : 'lazy' }}" decoding="async">
                @else
                    <div class="hero-slide__bg" style="background: var(--bg-dark-luxury);"></div>
                @endif

                <div class="hero-slide__overlay"></div>

                <div class="hero-slide__content">
                    <div class="hero-slide__copy">
                        <p class="hero-slide__kicker">{{ $slide['subtitle'] ?? 'Azraq Bridal' }}</p>
                        <h1 class="hero-slide__title">{{ $slide['title'] }}</h1>
                        @if (!empty($slide['body']))
                            <p class="hero-slide__body">{{ $slide['body'] }}</p>
                        @endif
                        <div class="hero-slide__actions">
                            @if (!empty($slide['cta_label']) && !empty($slide['cta_href']))
                                <a href="{{ $slide['cta_href'] }}" class="hero-cta-primary">
                                    {{ $slide['cta_label'] }}
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                                </a>
                            @endif
                            @if (!empty($slide['cta2_label']) && !empty($slide['cta2_href']))
                                <a href="{{ $slide['cta2_href'] }}" class="hero-cta-ghost">{{ $slide['cta2_label'] }}</a>
                            @endif
                        </div>
                    </div>

                    @if ($heroSlides->count() > 1)
                        <div class="hero-nav-panel">
                            <p class="hero-counter">
                                <strong x-text="String(current + 1).padStart(2, '0')">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</strong>
                                / {{ str_pad($heroSlides->count(), 2, '0', STR_PAD_LEFT) }}
                            </p>
                            <div class="hero-arrows">
                                <button @click.prevent="prev()" class="hero-arrow" aria-label="Previous">
                                    <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 3 5 8l5 5"/></svg>
                                </button>
                                <button @click.prevent="next()" class="hero-arrow" aria-label="Next">
                                    <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3l5 5-5 5"/></svg>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($heroSlides->count() > 1)
            <div class="hero-dots">
                @foreach ($heroSlides as $idx => $slide)
                    <button
                        @click="goTo({{ $idx }})"
                        :class="current === {{ $idx }} ? 'is-active' : ''"
                        class="hero-dot {{ $idx === 0 ? 'is-active' : '' }}"
                        aria-label="Slide {{ $idx + 1 }}"
                    ></button>
                @endforeach
            </div>
            <div
                x-bind:key="current"
                :class="!paused ? 'is-running' : ''"
                class="hero-progress"
            ></div>
        @endif
    </div>

    <script>
        function heroCarousel(slides) {
            return {
                slides, current: 0, paused: false, timer: null,
                get total() { return this.slides.length; },
                init() { if (this.total > 1) this.startTimer(); },
                startTimer() {
                    clearInterval(this.timer);
                    this.timer = setInterval(() => { if (!this.paused) this.next(); }, 4500);
                },
                next() { this.goTo((this.current + 1) % this.total); },
                prev() { this.goTo((this.current - 1 + this.total) % this.total); },
                goTo(i) { this.current = i; if (this.total > 1) this.startTimer(); },
                pause() { this.paused = true; },
                resume() { this.paused = false; },
            };
        }
    </script>


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
                    <div class="mx-auto aspect-[4/5] w-full max-w-[320px] overflow-hidden rounded-[var(--radius-3xl)] bg-[var(--bg-section-soft)] sm:aspect-[5/4] sm:max-w-none lg:aspect-auto lg:min-h-[440px]">
                        @php($nikahImageProduct = $latestNikahNama ?? $signatureNikah)
                        @php($customNikahImage = data_get($spotlightSection, 'settings.image_url'))
                        @php($nikahLayer = $customNikahImage ? null : $mockupLayerFor($nikahImageProduct ?: $signatureNikah))
                        @php($nikahImage = $customNikahImage ?: ($nikahLayer ? null : ($nikahImageProduct?->storefront_preview_image_url ?: $signatureNikah->storefront_preview_image_url)))
                        @if ($nikahLayer)
                            <div
                                class="relative h-full w-full overflow-hidden"
                                data-card-mockup-stage
                                data-map='@json($nikahLayer['map'])'
                                data-image-width="{{ $nikahLayer['mockup']->image_width ?: 1600 }}"
                                data-image-height="{{ $nikahLayer['mockup']->image_height ?: 1200 }}"
                            >
                                <img
                                    src="{{ $nikahLayer['mockup']->base_image_url }}"
                                    alt="{{ $nikahImageProduct?->name ?? $signatureNikah->name }}"
                                    class="product-card-lux__mockup-base absolute inset-0 h-full w-full object-cover"
                                    loading="lazy"
                                >
                                <img
                                    src="{{ $nikahLayer['flatArtwork'] }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="product-card-lux__mockup-template absolute left-0 top-0 h-full w-full object-fill"
                                    data-card-mockup-template
                                    loading="lazy"
                                >
                            </div>
                        @elseif ($nikahImage)
                            <img src="{{ $nikahImage }}" alt="{{ $nikahImageProduct?->name ?? $signatureNikah->name }}" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="flex min-w-0 flex-col items-center justify-center text-center lg:items-start lg:text-left">
                        <span class="section-kicker text-[0.62rem]">{{ $spotlightSection->subtitle ?? 'Signature Nikah Nama' }}</span>
                        <h2 class="mt-3 text-3xl font-semibold leading-[1.1] tracking-[-0.015em] text-[var(--text-main)] sm:text-4xl lg:text-5xl" style="font-family: 'Cormorant Garamond', Georgia, serif;">{{ $copy($spotlightSection->title, $signatureNikah->name) }}</h2>
                        <p class="mt-4 text-sm leading-7 text-[var(--text-muted)] sm:text-base">{{ \Illuminate\Support\Str::limit($copy($spotlightSection->content, $signatureNikah->description), 220) }}</p>

                        @if ($processSteps->isNotEmpty())
                            <div class="mt-6 process-rail justify-center lg:justify-start">
                                @foreach ($processSteps as $idx => $step)
                                    <span class="process-rail__step">{{ $step }}</span>
                                    @if (!$loop->last)
                                        <span class="process-rail__sep">·</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-7 flex flex-wrap justify-center gap-3 lg:justify-start">
                            <a href="{{ $signatureNikahHref }}" class="button-primary">{{ $spotlightSection->cta_label ?: 'Customize your Nikah' }}</a>
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
        <section
            id="curated"
            class="section-shell scroll-fade-in"
            x-data="{ activeMerchFilter: 'all' }"
        >
            <div class="container-shell">
                <x-storefront.section-header
                    :eyebrow="$productsSection->subtitle ?? 'Curated editions'"
                    :title="$copy($productsSection->title, 'Pieces we keep returning to.')"
                    :description="$copy($productsSection->content, 'Bridal wear, Nikah essentials, gifting combos, and bookings — curated weekly.')"
                    centered
                />

                <div class="mt-7 filter-pill-row">
                    @foreach ($curatedFilters as $filter)
                        <button
                            type="button"
                            class="filter-pill"
                            @click="activeMerchFilter = @js($filter['key']); $nextTick(() => $refs.merchandisingCarousel?.querySelector('.carousel-track')?.scrollTo({ left: 0, behavior: 'smooth' }))"
                            :class="activeMerchFilter === @js($filter['key']) ? 'filter-pill--active' : ''"
                            :aria-pressed="(activeMerchFilter === @js($filter['key'])).toString()"
                        >
                            {{ $filter['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-8">
                    <x-storefront.carousel :md-cols="3" :lg-cols="4" class="merchandising-carousel" x-ref="merchandisingCarousel">
                        @foreach ($curatedEditions as $product)
                            @php($productFilterKeys = $curatedFilterKeys($product))
                            <div
                                x-show="activeMerchFilter === 'all' || @js($productFilterKeys).includes(activeMerchFilter)"
                                x-transition.opacity.duration.180ms
                            >
                                <x-storefront.listing-card :product="$product" />
                            </div>
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
