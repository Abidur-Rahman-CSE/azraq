<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $pageTitle = $title ?? config('brand.name');
            $pageDescription = $description ?? config('brand.tagline');
            $canonical = $canonical ?? url()->current();
            $robots = $robots ?? 'index,follow';
            $socialImage = $socialImage ?? null;
            $schemaData = collect([
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => config('brand.name'),
                    'url' => url('/'),
                    'email' => config('brand.contact.email'),
                    'telephone' => config('brand.contact.phone'),
                ],
                ...($schemaData ?? []),
            ])->values()->all();
        @endphp
        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $pageDescription }}">
        <meta name="robots" content="{{ $robots }}">
        <link rel="canonical" href="{{ $canonical }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo/Azraq.svg') }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ config('brand.name') }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:url" content="{{ $canonical }}">
        @if ($socialImage)
            <meta property="og:image" content="{{ $socialImage }}">
        @endif
        <meta name="twitter:card" content="{{ $socialImage ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $pageTitle }}">
        <meta name="twitter:description" content="{{ $pageDescription }}">
        {{ $head ?? '' }}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script type="application/ld+json">{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    </head>
    <body class="app-shell font-sans antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-xl focus:bg-white focus:px-4 focus:py-3 focus:text-[var(--color-secondary-900)]">Skip to content</a>
        <x-storefront.announcement-bar />
        <x-storefront.header />

        <main id="main-content">
            @if (session('status'))
                <div class="container-shell pt-4 sm:pt-5">
                    <div class="rounded-[var(--radius-xl)] border border-[rgba(31,143,95,0.15)] bg-[rgba(31,143,95,0.08)] px-5 py-4 text-sm text-[var(--color-success)]">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="container-shell pt-4 sm:pt-5">
                    <div class="rounded-[var(--radius-xl)] border border-[rgba(180,35,24,0.18)] bg-[rgba(180,35,24,0.08)] px-5 py-4 text-sm text-[var(--color-danger)]">
                        <p class="font-semibold">Please review the highlighted issue{{ $errors->count() > 1 ? 's' : '' }}.</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>

        <x-storefront.footer />

        @stack('scripts')
    </body>
</html>
