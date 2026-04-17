@php
    $navItems = collect(config('commerce.storefront.nav', []));
    $mobileGroups = collect(config('commerce.storefront.mobile_nav_groups', []));
    $accountHref = route('account.index');
    $logoSrc = asset('images/logo/Azraq.svg');
@endphp

<header class="site-header" x-data="{ mobileOpen: false }">
    <div class="container-shell header-shell flex items-center justify-between gap-4 py-4 lg:grid lg:grid-cols-[auto_1fr_auto] lg:gap-6">
        <div class="flex items-center gap-3">
            <button type="button" class="header-icon-button header-menu-toggle" x-on:click="mobileOpen = true" aria-label="Open menu">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>

            <a href="{{ route('home') }}" class="brand-lockup shrink-0" aria-label="Azraq Bridal home">
                <span class="brand-mark">
                    <img src="{{ $logoSrc }}" alt="" class="h-full w-full object-contain">
                </span>
                <span class="brand-wordmark">
                    <span class="brand-wordmark-top">AZRAQ</span>
                    <span class="brand-wordmark-bottom">Bridal Collection</span>
                </span>
            </a>
        </div>

        <nav class="header-primary-nav items-center justify-center gap-6" aria-label="Primary">
            @foreach ($navItems as $item)
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
            @endforeach
        </nav>

        <div class="flex items-center justify-end gap-2 sm:gap-2.5">
            <a href="{{ route('search.index', ['search' => 'nikah']) }}" class="header-icon-button hidden lg:inline-flex" aria-label="Search">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                    <circle cx="11" cy="11" r="6"></circle>
                    <path d="M20 20l-3.5-3.5"></path>
                </svg>
            </a>
            <a href="{{ route('wishlist.index') }}" class="header-icon-button" aria-label="Wishlist">
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
            <a href="{{ $accountHref }}" class="header-icon-button hidden lg:inline-flex" aria-label="Account">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                    <circle cx="12" cy="8" r="3.5"></circle>
                    <path d="M5 20c1.6-3.3 4.1-5 7-5s5.4 1.7 7 5"></path>
                </svg>
            </a>
            <a href="{{ route('shop.index', ['type' => 'service']) }}" class="button-primary hidden 2xl:inline-flex">Book a Consultation</a>
        </div>
    </div>

    <div x-show="mobileOpen" x-cloak class="mobile-drawer" x-transition.opacity>
        <div class="mobile-drawer-panel" x-on:click.outside="mobileOpen = false">
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

            <div class="space-y-8 px-5 py-6">
                <div class="grid gap-3">
                    <a href="{{ route('shop.index') }}" class="button-primary w-full">Shop all products</a>
                    <a href="{{ route('shop.index', ['type' => 'service']) }}" class="button-secondary w-full">Book a Consultation</a>
                </div>

                @foreach ($mobileGroups as $group)
                    <section class="space-y-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--accent-primary)]">{{ $group['label'] }}</p>
                        <div class="grid gap-2">
                            @foreach ($group['items'] as $item)
                                <a href="{{ url($item['href']) }}" class="flex items-center justify-between rounded-[var(--radius-xl)] border border-[var(--border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--text-main)]">
                                    <span>{{ $item['label'] }}</span>
                                    <span class="text-[var(--text-muted)]">/</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <div class="grid gap-3 border-t border-[var(--border-soft)] pt-6">
                    <a href="{{ route('search.index') }}" class="button-ghost w-full">Search the catalog</a>
                    <a href="{{ route('cart.index') }}" class="button-ghost w-full">Open Cart</a>
                    <a href="{{ route('account.index') }}" class="button-ghost w-full">Account</a>
                </div>
            </div>
        </div>
    </div>
</header>
