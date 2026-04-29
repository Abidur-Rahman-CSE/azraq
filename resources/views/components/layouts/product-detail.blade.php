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

    <div class="relative pb-[var(--section-space-y)] pt-4 sm:pt-5 lg:pt-6">
        <div class="container-shell relative">
            {{ $slot }}
        </div>
    </div>
</x-layouts.storefront>
