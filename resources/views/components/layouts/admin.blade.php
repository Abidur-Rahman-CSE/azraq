<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Admin | '.config('brand.name') }}</title>
        <meta name="robots" content="noindex,nofollow">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[var(--color-surface-base)] font-sans text-[var(--color-text-main)] antialiased">
        <div class="grid min-h-screen lg:grid-cols-[280px_1fr]">
            <aside class="border-r border-[var(--color-border-soft)] bg-white/80 p-6 backdrop-blur">
                <div class="mb-8">
                    <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Azraq Bridal</p>
                    <h1 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Admin Foundation</h1>
                </div>
                <nav class="space-y-2 text-sm text-[var(--color-text-soft)]">
                    @foreach ([
                        ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
                        ['label' => 'Products', 'route' => 'admin.catalog.products.index'],
                        ['label' => 'Categories', 'route' => 'admin.catalog.categories.index'],
                        ['label' => 'Collections', 'route' => 'admin.catalog.collections.index'],
                        ['label' => 'Tags', 'route' => 'admin.catalog.tags.index'],
                        ['label' => 'Personalization', 'route' => 'admin.personalization.templates.index'],
                        ['label' => 'Inventory', 'route' => 'admin.inventory.index'],
                        ['label' => 'Orders', 'route' => 'admin.orders.index'],
                        ['label' => 'Bookings', 'route' => 'admin.bookings.index'],
                        ['label' => 'Homepage', 'route' => 'admin.content.homepage-sections.index'],
                        ['label' => 'FAQs', 'route' => 'admin.content.faqs.index'],
                        ['label' => 'Pages', 'route' => 'admin.content.pages.index'],
                        ['label' => 'Coupons', 'route' => 'admin.marketing.coupons.index'],
                        ['label' => 'Reviews', 'route' => 'admin.reviews.index'],
                        ['label' => 'Settings', 'route' => 'admin.settings.edit'],
                    ] as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            @class([
                                'block rounded-2xl px-4 py-3 transition hover:bg-[var(--color-surface-cream)] hover:text-[var(--color-secondary-900)]',
                                'bg-[var(--color-surface-cream)] text-[var(--color-secondary-900)]' => request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])),
                            ])
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <main class="p-6 lg:p-10">
                @if (session('status'))
                    <div class="mb-6 rounded-[var(--radius-xl)] border border-[rgba(31,143,95,0.15)] bg-[rgba(31,143,95,0.08)] px-5 py-4 text-sm text-[var(--color-success)]">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
