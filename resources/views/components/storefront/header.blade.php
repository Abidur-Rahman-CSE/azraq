@php
    use App\Models\Category;
    use Illuminate\Support\Facades\Cache;

    $navItems = collect(config('commerce.storefront.nav', []));
    $mobileGroups = collect(config('commerce.storefront.mobile_nav_groups', []));
    $accountHref = route('account.index');
    $logoSrc = asset('images/logo/Azraq.svg');
    $navCategories = collect(Cache::remember('storefront.header.nav_categories.v2', now()->addHours(4), function () {
        return Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category) => [
                'id' => (int) $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->values()
            ->all();
    }));
@endphp

<header
    class="site-header"
    x-data="{ mobileOpen: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', mobileOpen)"
    @keydown.escape.window="mobileOpen = false"
    @resize.window="if (window.innerWidth >= 1024) mobileOpen = false"
>
    <div class="container-shell header-shell flex items-center justify-between gap-4 py-4 lg:grid lg:grid-cols-[auto_1fr_auto] lg:gap-6">
        <div class="flex min-w-0 items-center gap-3">
            <button type="button" class="header-icon-button lg:hidden" x-on:click.stop="mobileOpen = true" aria-label="Open menu" :aria-expanded="mobileOpen ? 'true' : 'false'" aria-controls="mobile-navigation-drawer">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>

            <a href="{{ route('home') }}" class="brand-lockup min-w-0" aria-label="Azraq Bridal home">
                <span class="brand-mark">
                    <img src="{{ $logoSrc }}" alt="" class="h-full w-full object-contain">
                </span>
                <span class="brand-wordmark">
                    <span class="brand-wordmark-top">AZRAQ</span>
                    <span class="brand-wordmark-bottom">Bridal Collection</span>
                </span>
            </a>
        </div>

        <nav class="hidden items-center justify-center gap-5 xl:flex" aria-label="Primary">
            @foreach ($navItems as $item)
                @if ($item['label'] === 'Categories')
                    <div
                        class="relative"
                        x-data="{ open: false, closeTimer: null }"
                        @mouseenter="clearTimeout(closeTimer); open = true"
                        @mouseleave="closeTimer = setTimeout(() => open = false, 120)"
                    >
                        <button
                            type="button"
                            class="header-nav-link inline-flex items-center gap-2"
                            :class="open ? 'is-active' : ''"
                            @click="open = !open"
                            aria-haspopup="true"
                            :aria-expanded="open ? 'true' : 'false'"
                        >
                            <span>{{ $item['label'] }}</span>
                            <svg class="h-3.5 w-3.5 transition duration-200 ease-out" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="m5 7.5 5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <div
                            x-cloak
                            x-show="open"
                            x-transition.opacity.duration.150ms
                            class="absolute left-1/2 top-full z-40 mt-3 w-72 -translate-x-1/2 rounded-[var(--radius-xl)] border border-[var(--border-soft)] bg-white/98 p-2 shadow-[0_18px_60px_rgba(0,0,0,0.08)] backdrop-blur"
                        >
                            <div class="space-y-1">
                                @foreach ($navCategories as $category)
                                    <a
                                        href="{{ route('categories.show', $category['slug']) }}"
                                        class="flex items-center justify-between rounded-[var(--radius-lg)] px-3 py-2.5 text-sm font-medium text-[var(--text-main)] transition duration-200 ease-out hover:bg-[var(--bg-section-soft)] hover:text-[var(--accent-primary)]"
                                    >
                                        <span>{{ $category['name'] }}</span>
                                        <span class="text-[var(--text-muted)]">/</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    @php($href = str_starts_with($item['href'], '/') ? url($item['href']) : $item['href'])
                    <a
                        href="{{ $href }}"
                        @class([
                            'header-nav-link',
                            'is-active' => request()->fullUrlIs($href) || request()->is(ltrim(parse_url($href, PHP_URL_PATH) ?: '', '/')),
                        ])
                    >
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center justify-end gap-1.5 sm:gap-2 lg:gap-2.5">
            <a href="{{ route('search.index', ['search' => 'nikah']) }}" class="header-icon-button" aria-label="Search">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                    <circle cx="11" cy="11" r="6"></circle>
                    <path d="M20 20l-3.5-3.5"></path>
                </svg>
            </a>
            <a href="{{ route('wishlist.index') }}" class="header-icon-button hidden lg:inline-flex" aria-label="Wishlist">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                    <path d="M12 20s-7-4.35-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.65-7 10-7 10Z"></path>
                </svg>
            </a>
            <a href="{{ route('cart.index') }}" class="header-icon-button" aria-label="Cart">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                    <path d="M4 5h2l1.6 8.2a1 1 0 0 0 1 .8h7.9a1 1 0 0 0 1-.74L20 7H7.2"></path>
                    <circle cx="10" cy="19" r="1.2"></circle>
                    <circle cx="17" cy="19" r="1.2"></circle>
                </svg>
            </a>
            <a href="{{ $accountHref }}" class="header-icon-button header-account-button hidden lg:inline-flex" aria-label="Account">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                    <circle cx="12" cy="8" r="3.5"></circle>
                    <path d="M5 20c1.6-3.3 4.1-5 7-5s5.4 1.7 7 5"></path>
                </svg>
                <span class="header-account-button__label">Account</span>
            </a>
            <a href="{{ route('shop.index', ['type' => 'service']) }}" class="button-primary hidden 2xl:inline-flex">Book a Consultation</a>
        </div>
    </div>

    <div
        id="mobile-navigation-drawer"
        x-show="mobileOpen"
        x-cloak
        class="mobile-drawer lg:hidden"
        x-transition.opacity
        x-on:click.self="mobileOpen = false"
        role="dialog"
        aria-modal="true"
        aria-label="Mobile navigation"
    >
        <div
            class="mobile-drawer-panel"
            x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            x-on:click.stop
        >
            <div class="flex items-center justify-between border-b border-[var(--border-soft)] px-5 py-5">
                <a href="{{ route('home') }}" class="brand-lockup shrink-0">
                    <span class="brand-mark">
                        <img src="{{ $logoSrc }}" alt="" class="h-full w-full object-contain">
                    </span>
                    <span class="brand-wordmark">
                        <span class="brand-wordmark-top">AZRAQ</span>
                        <span class="brand-wordmark-bottom">Bridal Collection</span>
                    </span>
                </a>
                <button type="button" class="header-icon-button" x-on:click="mobileOpen = false" aria-label="Close menu">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>

            <div class="space-y-8 px-5 py-6 pb-28">
                @foreach ($mobileGroups as $group)
                    <section class="space-y-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--accent-primary)]">{{ $group['label'] }}</p>
                        <div class="space-y-2">
                            @foreach ($group['items'] as $item)
                                <a href="{{ url($item['href']) }}" class="flex items-center justify-between rounded-[var(--radius-xl)] border border-[var(--border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--text-main)]">
                                    <span>{{ $item['label'] }}</span>
                                    <span class="text-[var(--text-muted)]">/</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="space-y-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--accent-primary)]">Categories</p>
                    <div class="space-y-2">
                        @foreach ($navCategories as $category)
                            <a href="{{ route('categories.show', $category['slug']) }}" class="flex items-center justify-between rounded-[var(--radius-xl)] border border-[var(--border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--text-main)]">
                                <span>{{ $category['name'] }}</span>
                                <span class="text-[var(--text-muted)]">/</span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="header-account-card">
                    <p class="header-account-card__title">Account</p>
                    <div class="header-account-card__links">
                        <a href="{{ route('wishlist.index') }}" class="header-account-card__link">
                            <span>Wishlist</span>
                            <span class="text-(--text-muted)">/</span>
                        </a>
                        <a href="{{ $accountHref }}" class="header-account-card__link">
                            <span>Account hub</span>
                            <span class="text-(--text-muted)">/</span>
                        </a>
                    </div>
                </section>
            </div>

            <div class="mobile-drawer-cta">
                <a href="{{ route('shop.index', ['type' => 'service']) }}" class="button-primary w-full">Book a Consultation</a>
                <a href="{{ route('cart.index') }}" class="button-secondary w-full">Open Cart</a>
            </div>
        </div>
    </div>
</header>
