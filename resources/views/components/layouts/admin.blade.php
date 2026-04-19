!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Azura Admin | ' . config('brand.name') }}</title>
    <meta name="robots" content="noindex,nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[var(--color-surface-base)] font-sans text-[var(--color-text-main)] antialiased">
    <div class="flex min-h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside
            class="hidden lg:flex lg:w-72 lg:border-r lg:border-[var(--color-border-soft)] lg:bg-white lg:sticky lg:top-0">
            <div class="flex h-16 shrink-0 items-center px-6">
                <span class="text-xl font-bold tracking-tight text-[var(--color-secondary-900)]">Azura Admin</span>
            </div>
            <div class="flex flex-1 flex-col overflow-y-auto px-4 py-4">
                <nav class="flex flex-1 flex-col space-y-8">
                    <div>
                        <ul role="list" class="space-y-2">
                            <li>
                                <a href="{{ route('admin.dashboard') }}" @class([
                                    'group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm font-semibold',
                                    'bg-[var(--color-surface-cream)] text-[var(--color-secondary-900)]' => request()->routeIs(
                                        'admin.dashboard'),
                                ])>
                                    <span class="text-gray-400">Dashboard</span>
                                </a>
                            </li>
                            <li class="mt-4">
                                <h3 class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Catalog
                                </h3>
                                <ul role="list" class="mt-2 space-y-1">
                                    @foreach ([['label' => 'Products', 'route' => 'admin.catalog.products.index'], ['label' => 'Categories', 'route' => 'admin.catalog.categories.index'], ['label' => 'Collections', 'route' => 'admin.catalog.collections.index']] as $item)
                                        <li>
                                            <a href="{{ route($item['route']) }}" @class([
                                                'group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm font-medium',
                                                'bg-[var(--color-surface-cream)] text-[var(--color-secondary-900)]' =>
                                                    request()->routeIs($item['route']) ||
                                                    request()->routeIs(str_replace('.index', '.*', $item['route'])),
                                            ])>
                                                {{ $item['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                            <li class="mt-4">
                                <h3 class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Operations
                                </h3>
                                <ul role="list" class="mt-2 space-y-1">
                                    @foreach ([['label' => 'Orders', 'route' => 'admin.orders.index'], ['label' => 'Bookings', 'route' => 'admin.bookings.index'], ['label' => 'Inventory', 'route' => 'admin.inventory.index']] as $item)
                                        <li>
                                            <a href="{{ route($item['route']) }}" @class([
                                                'group flex items-center gap-x-3 rounded-md px-3 py-2 text-sm font-medium',
                                                'bg-[var(--color-surface-cream)] text-[var(--color-secondary-900)]' =>
                                                    request()->routeIs($item['route']) ||
                                                    request()->routeIs(str_replace('.index', '.*', $item['route'])),
                                            ])>
                                                {{ $item['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Header -->
            <header
                class="bg-white border-b border-[var(--color-border-soft)] h-16 flex items-center justify-between px-8 sticky top-0 z-10">
                <div class="flex items-center text-sm text-gray-500">
                    <span class="text-gray-900 font-medium">Admin</span>
                    <span class="mx-2">/</span>
                    <span>Overview</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="h-8 w-8 rounded-full bg-gray-200"></div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6 lg:p-10 bg-gray-50/50">
                <div class="max-w-7xl mx-auto">
                    @if (session('status'))
                        <div
                            class="mb-6 rounded-[var(--radius-xl)] border border-[rgba(31,143,95,0.15)] bg-[rgba(31,143,95,0.08)] px-5 py-4 text-sm text-[var(--color-success)]">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>

</html>
