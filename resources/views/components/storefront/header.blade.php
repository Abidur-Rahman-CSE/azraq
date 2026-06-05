@php
    use App\Models\Category;
    use App\Models\Collection;
    use Illuminate\Support\Facades\Cache;

    $navItems = collect(config('commerce.storefront.nav', []));
    $mobileGroups = collect(config('commerce.storefront.mobile_nav_groups', []));
    $accountHref = route('account.index');
    $logoSrc = asset('images/logo/Azraq.svg');
    $mobileItemIcons = [
        'Home' => 'home',
        'All Products' => 'bag',
        'About' => 'sparkles',
        'Track Order' => 'bag',
        'Contact' => 'sparkles',
        'Collections' => 'grid',
        'Wishlist' => 'heart',
        'Account' => 'user',
        'Consultation' => 'sparkles',
    ];
    $navCategories = collect(Cache::remember('storefront.header.nav_categories.v3', now()->addHours(4), function () {
        return Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'parent_id'])
            ->map(fn (Category $category) => [
                'id' => (int) $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'children' => $category->children->map(fn (Category $child) => [
                    'id' => (int) $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }));
    $navCollections = collect(Cache::remember('storefront.header.nav_collections.v1', now()->addHours(4), function () {
        return Collection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Collection $collection) => [
                'id' => (int) $collection->id,
                'name' => $collection->name,
                'slug' => $collection->slug,
            ])
            ->values()
            ->all();
    }));
@endphp

@once
    @php
        $renderDrawerIcon = function (string $icon): string {
            return match ($icon) {
                'home' => '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"></path><path d="M5.5 9.5V21h13V9.5"></path></svg>',
                'bag' => '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6.5 8h11l1.2 11.2a1 1 0 0 1-1 .8H6.3a1 1 0 0 1-1-.8L6.5 8Z"></path><path d="M9 9V7a3 3 0 0 1 6 0v2"></path></svg>',
                'grid' => '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="4" width="6" height="6" rx="1.2"></rect><rect x="14" y="4" width="6" height="6" rx="1.2"></rect><rect x="4" y="14" width="6" height="6" rx="1.2"></rect><rect x="14" y="14" width="6" height="6" rx="1.2"></rect></svg>',
                'sparkles' => '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m12 3 1.7 4.8L18.5 9.5l-4.8 1.7L12 16l-1.7-4.8L5.5 9.5l4.8-1.7L12 3Z"></path><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8L19 15Z"></path></svg>',
                'heart' => '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 20s-7-4.35-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.65-7 10-7 10Z"></path></svg>',
                'user' => '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.5"></circle><path d="M5 20c1.6-3.3 4.1-5 7-5s5.4 1.7 7 5"></path></svg>',
                default => '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14"></path><path d="m13 6 6 6-6 6"></path></svg>',
            };
        };
    @endphp
@endonce

<header
    class="site-header"
    x-data="{ mobileOpen: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', mobileOpen)"
    @keydown.escape.window="mobileOpen = false"
>
    <div class="container-shell header-shell flex items-center justify-between gap-3 py-3 lg:grid lg:grid-cols-[auto_1fr_auto] lg:gap-6 lg:py-4">
        <div class="flex min-w-0 items-center">
            <a href="{{ route('home') }}" class="brand-lockup min-w-0" aria-label="Azraq Bridal home">
                <span class="brand-mark">
                    <img src="{{ $logoSrc }}" alt="" class="h-full w-full object-contain">
                </span>
                <span class="brand-wordmark">
                    <span class="brand-wordmark-top">AZRAQ</span>
                </span>
            </a>
        </div>

        <nav class="hidden items-center justify-center gap-5 xl:flex" aria-label="Primary">
            @foreach ($navItems as $item)
                @if ($item['label'] === 'Collections')
                    <div
                        class="relative"
                        x-data="{ open: false, closeTimer: null }"
                        @mouseenter="clearTimeout(closeTimer); open = true"
                        @mouseleave="closeTimer = setTimeout(() => open = false, 120)"
                    >
                        <button
                            type="button"
                            class="header-nav-link inline-flex items-center gap-2"
                            :class="open || {{ request()->routeIs('collections.show') ? 'true' : 'false' }} ? 'is-active' : ''"
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
                                @forelse ($navCollections as $collection)
                                    <a
                                        href="{{ route('collections.show', $collection['slug']) }}"
                                        class="block rounded-[var(--radius-lg)] px-3 py-2.5 text-sm font-medium text-[var(--text-main)] transition duration-200 ease-out hover:bg-[var(--bg-section-soft)] hover:text-[var(--accent-primary)]"
                                    >
                                        {{ $collection['name'] }}
                                    </a>
                                @empty
                                    <a
                                        href="{{ route('shop.index') }}"
                                        class="block rounded-[var(--radius-lg)] px-3 py-2.5 text-sm font-medium text-[var(--text-main)] transition duration-200 ease-out hover:bg-[var(--bg-section-soft)] hover:text-[var(--accent-primary)]"
                                    >
                                        All products
                                    </a>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @elseif ($item['label'] === 'Categories')
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
                                    @php($children = collect($category['children'] ?? []))
                                    <div class="group/category relative">
                                        <a
                                            href="{{ route('categories.show', $category['slug']) }}"
                                            class="flex items-center justify-between rounded-[var(--radius-lg)] px-3 py-2.5 text-sm font-medium text-[var(--text-main)] transition duration-200 ease-out hover:bg-[var(--bg-section-soft)] hover:text-[var(--accent-primary)]"
                                        >
                                            <span>{{ $category['name'] }}</span>
                                            @if ($children->isNotEmpty())
                                                <span class="text-[var(--text-muted)] transition group-hover/category:text-[var(--accent-primary)]">&gt;</span>
                                            @endif
                                        </a>

                                        @if ($children->isNotEmpty())
                                            <div class="absolute left-full top-0 z-50 ml-2 hidden min-w-60 rounded-[var(--radius-xl)] border border-[var(--border-soft)] bg-white/98 p-2 shadow-[0_18px_60px_rgba(0,0,0,0.08)] backdrop-blur group-hover/category:block">
                                                @foreach ($children as $child)
                                                    <a
                                                        href="{{ route('categories.show', $child['slug']) }}"
                                                        class="block rounded-[var(--radius-lg)] px-3 py-2.5 text-sm font-medium text-[var(--text-main)] transition duration-200 ease-out hover:bg-[var(--bg-section-soft)] hover:text-[var(--accent-primary)]"
                                                    >
                                                        {{ $child['name'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
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

        <div class="flex shrink-0 items-center justify-end gap-1.5 sm:gap-2 lg:gap-2.5">
            <a href="{{ route('search.index') }}" class="header-icon-button" aria-label="Search">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                    <circle cx="11" cy="11" r="6"></circle>
                    <path d="M20 20l-3.5-3.5"></path>
                </svg>
            </a>
            <a href="{{ route('wishlist.index') }}" class="header-icon-button header-action-lg" aria-label="Wishlist">
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
            <a href="{{ $accountHref }}" class="header-icon-button header-action-lg" aria-label="Account">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                    <circle cx="12" cy="8" r="3.5"></circle>
                    <path d="M5 20c1.6-3.3 4.1-5 7-5s5.4 1.7 7 5"></path>
                </svg>
            </a>
            <button type="button" class="header-icon-button header-menu-toggle" x-on:click.stop="mobileOpen = true" aria-label="Open menu" :aria-expanded="mobileOpen ? 'true' : 'false'" aria-controls="mobile-navigation-drawer">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
            <a href="{{ route('shop.index', ['type' => 'service']) }}" class="button-primary header-action-2xl">Book a Consultation</a>
        </div>
    </div>

    <div
        id="mobile-navigation-drawer"
        x-show="mobileOpen"
        x-cloak
        class="mobile-drawer header-drawer"
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
            <div class="flex items-center justify-between border-b border-[var(--border-soft)] px-4 py-4">
                <a href="{{ route('home') }}" class="brand-lockup min-w-0">
                    <span class="brand-mark">
                        <img src="{{ $logoSrc }}" alt="" class="h-full w-full object-contain">
                    </span>
                    <span class="brand-wordmark">
                        <span class="brand-wordmark-top">AZRAQ</span>
                    </span>
                </a>
                <button type="button" class="header-icon-button" x-on:click="mobileOpen = false" aria-label="Close menu">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>

            <div class="space-y-5 px-4 py-4 pb-8">
                @foreach ($mobileGroups as $group)
                    <section class="space-y-3">
                        <p class="mobile-drawer-section-title">{{ $group['label'] }}</p>
                        <div class="space-y-1.5">
                            @foreach ($group['items'] as $item)
                                @php($icon = $mobileItemIcons[$item['label']] ?? 'sparkles')
                                <a href="{{ url($item['href']) }}" class="mobile-drawer-link" @click="mobileOpen = false">
                                    <span class="mobile-drawer-link__lead">
                                        <span class="mobile-drawer-link__icon">{!! $renderDrawerIcon($icon) !!}</span>
                                        <span>{{ $item['label'] }}</span>
                                    </span>
                                    <span class="mobile-drawer-link__chevron" aria-hidden="true">/</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="space-y-2" x-data="{ open: false }">
                    <button type="button" class="mobile-drawer-link mobile-drawer-link--toggle" @click="open = !open" :aria-expanded="open ? 'true' : 'false'">
                        <span class="mobile-drawer-link__lead">
                            <span class="mobile-drawer-link__icon">{!! $renderDrawerIcon('grid') !!}</span>
                            <span>Collections</span>
                        </span>
                        <svg class="mobile-drawer-link__accordion" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="m5 7.5 5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div x-cloak x-show="open" x-transition.duration.180ms class="mobile-drawer-sublist">
                        @forelse ($navCollections as $collection)
                            <a href="{{ route('collections.show', $collection['slug']) }}" class="mobile-drawer-sublink" @click="mobileOpen = false">
                                <span>{{ $collection['name'] }}</span>
                            </a>
                        @empty
                            <a href="{{ route('shop.index') }}" class="mobile-drawer-sublink" @click="mobileOpen = false">
                                <span>All products</span>
                            </a>
                        @endforelse
                    </div>
                </section>

                <section class="space-y-2" x-data="{ open: false, openCategory: null }">
                    <button type="button" class="mobile-drawer-link mobile-drawer-link--toggle" @click="open = !open" :aria-expanded="open ? 'true' : 'false'">
                        <span class="mobile-drawer-link__lead">
                            <span class="mobile-drawer-link__icon">{!! $renderDrawerIcon('grid') !!}</span>
                            <span>Categories</span>
                        </span>
                        <svg class="mobile-drawer-link__accordion" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="m5 7.5 5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div x-cloak x-show="open" x-transition.duration.180ms class="mobile-drawer-sublist">
                        @foreach ($navCategories as $category)
                            @php($children = collect($category['children'] ?? []))
                            @if ($children->isNotEmpty())
                                <div>
                                    <button type="button" class="mobile-drawer-sublink w-full" @click="openCategory = openCategory === {{ $category['id'] }} ? null : {{ $category['id'] }}">
                                        <span>{{ $category['name'] }}</span>
                                        <svg class="h-4 w-4 transition-transform duration-200" :class="openCategory === {{ $category['id'] }} ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path d="m7.5 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <div x-cloak x-show="openCategory === {{ $category['id'] }}" x-transition.duration.160ms class="ml-4 mt-1 space-y-1 border-l border-[var(--border-soft)] pl-3">
                                        <a href="{{ route('categories.show', $category['slug']) }}" class="mobile-drawer-sublink" @click="mobileOpen = false">
                                            <span>All {{ $category['name'] }}</span>
                                        </a>
                                        @foreach ($children as $child)
                                            <a href="{{ route('categories.show', $child['slug']) }}" class="mobile-drawer-sublink" @click="mobileOpen = false">
                                                <span>{{ $child['name'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ route('categories.show', $category['slug']) }}" class="mobile-drawer-sublink" @click="mobileOpen = false">
                                    <span>{{ $category['name'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </section>

                <section class="space-y-3">
                    <p class="mobile-drawer-section-title">More</p>
                    <div class="space-y-1.5">
                        <a href="{{ route('wishlist.index') }}" class="mobile-drawer-link" @click="mobileOpen = false">
                            <span class="mobile-drawer-link__lead">
                                <span class="mobile-drawer-link__icon">{!! $renderDrawerIcon('heart') !!}</span>
                                <span>Wishlist</span>
                            </span>
                            <span class="mobile-drawer-link__chevron" aria-hidden="true">/</span>
                        </a>
                        <a href="{{ $accountHref }}" class="mobile-drawer-link" @click="mobileOpen = false">
                            <span class="mobile-drawer-link__lead">
                                <span class="mobile-drawer-link__icon">{!! $renderDrawerIcon('user') !!}</span>
                                <span>Account</span>
                            </span>
                            <span class="mobile-drawer-link__chevron" aria-hidden="true">/</span>
                        </a>
                        <a href="{{ route('shop.index', ['type' => 'service']) }}" class="mobile-drawer-link" @click="mobileOpen = false">
                            <span class="mobile-drawer-link__lead">
                                <span class="mobile-drawer-link__icon">{!! $renderDrawerIcon('sparkles') !!}</span>
                                <span>Consultation</span>
                            </span>
                            <span class="mobile-drawer-link__chevron" aria-hidden="true">/</span>
                        </a>
                    </div>
                </section>
            </div>

        </div>
    </div>
</header>
