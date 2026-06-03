@php
    $navigationGroups = [
        [
            'label' => 'Overview',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => ['admin.dashboard']],
            ],
        ],
        [
            'label' => 'Catalog',
            'items' => [
                ['label' => 'Products', 'route' => 'admin.catalog.products.index', 'match' => ['admin.catalog.products.*']],
                ['label' => 'Categories', 'route' => 'admin.catalog.categories.index', 'match' => ['admin.catalog.categories.*']],
                ['label' => 'Collections', 'route' => 'admin.catalog.collections.index', 'match' => ['admin.catalog.collections.*']],
                ['label' => 'Tags', 'route' => 'admin.catalog.tags.index', 'match' => ['admin.catalog.tags.*']],
            ],
        ],
        [
            'label' => 'Personalization',
            'items' => [
                ['label' => 'Templates', 'route' => 'admin.personalization.templates.index', 'match' => ['admin.personalization.templates.*']],
                ['label' => 'Fonts', 'route' => 'admin.personalization.fonts.index', 'match' => ['admin.personalization.fonts.*']],
                ['label' => 'Mockups', 'route' => 'admin.mockups.index', 'match' => ['admin.mockups.*']],
            ],
        ],
        [
            'label' => 'Operations',
            'items' => [
                ['label' => 'Inventory', 'route' => 'admin.inventory.index', 'match' => ['admin.inventory.*']],
                ['label' => 'Orders', 'route' => 'admin.orders.index', 'match' => ['admin.orders.*']],
                ['label' => 'Bookings', 'route' => 'admin.bookings.index', 'match' => ['admin.bookings.*']],
                ['label' => 'Users', 'route' => 'admin.users.index', 'match' => ['admin.users.*']],
            ],
        ],
        [
            'label' => 'Content',
            'items' => [
                ['label' => 'Homepage', 'route' => 'admin.content.homepage-sections.index', 'match' => ['admin.content.homepage-sections.*']],
                ['label' => 'FAQs', 'route' => 'admin.content.faqs.index', 'match' => ['admin.content.faqs.*']],
                ['label' => 'Pages', 'route' => 'admin.content.pages.index', 'match' => ['admin.content.pages.*']],
                ['label' => 'Coupons', 'route' => 'admin.marketing.coupons.index', 'match' => ['admin.marketing.coupons.*']],
                ['label' => 'Settings', 'route' => 'admin.settings.edit', 'match' => ['admin.settings.*']],
            ],
        ],
    ];

    $currentLabel = 'Admin';
    $currentGroup = 'Control panel';

    foreach ($navigationGroups as $group) {
        foreach ($group['items'] as $item) {
            $patterns = $item['match'] ?? [$item['route']];
            if (collect($patterns)->contains(fn (string $pattern) => request()->routeIs($pattern))) {
                $currentLabel = $item['label'];
                $currentGroup = $group['label'];
            }
        }
    }

    $fallbackBreadcrumbs = [
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
    ];

    if ($currentGroup !== 'Overview') {
        $fallbackBreadcrumbs[] = ['label' => $currentGroup];
    }

    if ($currentLabel !== 'Dashboard') {
        $fallbackBreadcrumbs[] = ['label' => $currentLabel];
    }

    $pageTitle = $pageTitle ?? $currentLabel;
    $pageSubtitle = $pageSubtitle ?? ($currentGroup === 'Overview' ? 'Azraq Bridal admin foundation' : $currentGroup.' workspace');
    $breadcrumbs = isset($breadcrumbs) && count($breadcrumbs) ? $breadcrumbs : $fallbackBreadcrumbs;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Admin | '.config('brand.name') }}</title>
        <meta name="robots" content="noindex,nofollow">
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo/Azraq.svg') }}">
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="admin-shell min-h-screen font-sans text-[var(--color-text-main)] antialiased" x-data="{ sidebarOpen: false, profileOpen: false }">
        <div class="admin-shell__frame">
            <div
                class="admin-shell__overlay"
                x-cloak
                x-show="sidebarOpen"
                x-transition.opacity
                @click="sidebarOpen = false"
            ></div>

            <aside class="admin-sidebar" :class="{ 'is-open': sidebarOpen }">
                <div class="admin-sidebar__header">
                    <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                        <span class="admin-brand__mark">
                            <img src="{{ asset('images/logo/Azraq.svg') }}" alt="Azraq Bridal">
                        </span>
                        <span class="admin-brand__wordmark">
                            <span class="admin-brand__eyebrow">Azraq Bridal</span>
                            <span class="admin-brand__title">Admin Foundation</span>
                        </span>
                    </a>

                    <button type="button" class="admin-mobile-icon-button lg:hidden" @click="sidebarOpen = false" aria-label="Close admin navigation">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="admin-sidebar__meta">
                    <p>Premium operational control for catalog, personalization, proofing, and content.</p>
                </div>

                <nav class="admin-sidebar__nav">
                    @foreach ($navigationGroups as $group)
                        <div class="admin-nav-group">
                            <p class="admin-nav-group__label">{{ $group['label'] }}</p>
                            <div class="admin-nav-group__items">
                                @foreach ($group['items'] as $item)
                                    @php
                                        $isActive = collect($item['match'] ?? [$item['route']])->contains(
                                            fn (string $pattern) => request()->routeIs($pattern)
                                        );
                                    @endphp
                                    <a
                                        href="{{ route($item['route']) }}"
                                        @class([
                                            'admin-nav-link',
                                            'is-active' => $isActive,
                                        ])
                                    >
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>

                <div class="admin-sidebar__footer">
                    <div class="admin-sidebar__support surface-card-soft p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Current focus</p>
                        <p class="mt-3 text-sm leading-7 text-[var(--color-text-soft)]">Media workflows, Nikah template tooling, and order proof readiness are next in this upgrade pass.</p>
                    </div>
                </div>
            </aside>

            <div class="admin-main">
                <header class="admin-topbar">
                    <div class="admin-topbar__inner">
                        <div class="flex items-center gap-3">
                            <button type="button" class="admin-mobile-icon-button lg:hidden" @click="sidebarOpen = true" aria-label="Open admin navigation">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                                </svg>
                            </button>

                            <div>
                                <p class="admin-topbar__eyebrow">{{ $pageSubtitle }}</p>
                                <h1 class="admin-topbar__title">{{ $pageTitle }}</h1>
                            </div>
                        </div>

                        <div class="admin-topbar__actions">
                            <label class="admin-search hidden xl:flex">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[var(--color-secondary-900)]/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                </svg>
                                <input type="text" placeholder="Search products, orders, or templates" disabled>
                            </label>

                            <div class="relative">
                                <button type="button" class="admin-profile-button" @click="profileOpen = ! profileOpen" :aria-expanded="profileOpen.toString()">
                                    <span class="admin-profile-button__avatar">{{ str(auth()->user()?->name ?? 'A')->substr(0, 1)->upper() }}</span>
                                    <span class="hidden text-left sm:block">
                                        <span class="block text-sm font-semibold text-[var(--color-secondary-900)]">{{ auth()->user()?->name ?? 'Azraq Admin' }}</span>
                                        <span class="block text-xs text-[var(--color-text-soft)]">Internal workspace</span>
                                    </span>
                                </button>

                                <div
                                    class="admin-profile-menu"
                                    x-cloak
                                    x-show="profileOpen"
                                    x-transition.opacity.scale.origin.top.right
                                    @click.outside="profileOpen = false"
                                >
                                    <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ auth()->user()?->name ?? 'Azraq Admin' }}</p>
                                    <p class="mt-1 text-sm text-[var(--color-text-soft)]">{{ auth()->user()?->email }}</p>
                                    <div class="mt-4 flex flex-col gap-3">
                                        <a href="{{ route('home') }}" class="inline-flex text-sm font-semibold text-[var(--color-secondary-900)]">Open storefront</a>
                                        <form method="POST" action="{{ route('admin.logout') }}">
                                            @csrf
                                            <button type="submit" class="text-sm font-semibold text-[var(--color-primary-900)]">Sign out</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-topbar__breadcrumbs">
                        @foreach ($breadcrumbs as $crumb)
                            @if (! $loop->first)
                                <span>/</span>
                            @endif

                            @if (! empty($crumb['href']))
                                <a href="{{ $crumb['href'] }}">{{ $crumb['label'] }}</a>
                            @else
                                <span class="is-current">{{ $crumb['label'] }}</span>
                            @endif
                        @endforeach
                    </div>
                </header>

                <main class="admin-content">
                    @if (session('status'))
                        <div class="admin-flash admin-flash--success">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
