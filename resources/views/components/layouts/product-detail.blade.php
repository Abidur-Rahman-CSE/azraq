@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'robots' => null,
    'schemaData' => [],
    'socialImage' => null,
])

<x-layouts.storefront
    :title="$title ?? config('brand.name')"
    :description="$description ?? config('brand.tagline')"
    :canonical="$canonical"
    :robots="$robots"
    :schema-data="$schemaData"
    :social-image="$socialImage"
>
    @if (isset($head))
        <x-slot:head>
            {{ $head }}
        </x-slot:head>
    @endif

    <div class="relative overflow-hidden bg-[#FAFAF8] pb-[var(--section-space-y)] pt-6 sm:pt-8 lg:pt-10">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-56 bg-[radial-gradient(circle_at_top,_rgba(196,168,130,0.14),transparent_60%)]"></div>
        <div class="pointer-events-none absolute -right-10 top-24 h-64 w-64 rounded-full bg-[rgba(139,38,53,0.05)] blur-3xl"></div>
        <div class="pointer-events-none absolute -left-10 bottom-24 h-56 w-56 rounded-full bg-[rgba(44,44,62,0.04)] blur-3xl"></div>

        <div class="container-shell relative">
            {{ $slot }}
        </div>
    </div>
</x-layouts.storefront>
