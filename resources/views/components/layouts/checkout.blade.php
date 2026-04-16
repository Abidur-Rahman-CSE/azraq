@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'robots' => null,
    'schemaData' => [],
    'socialImage' => null,
])

<x-layouts.storefront
    :title="$title ?? 'Checkout | '.config('brand.name')"
    :description="$description ?? config('brand.tagline')"
    :canonical="$canonical"
    :robots="$robots"
    :schema-data="$schemaData"
    :social-image="$socialImage"
>
    <div class="section-shell">
        <div class="container-shell max-w-6xl">
            <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-layouts.storefront>
