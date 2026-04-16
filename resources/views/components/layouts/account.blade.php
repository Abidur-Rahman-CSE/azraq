@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'robots' => null,
    'schemaData' => [],
    'socialImage' => null,
])

<x-layouts.storefront
    :title="$title ?? 'Account | '.config('brand.name')"
    :description="$description ?? config('brand.tagline')"
    :canonical="$canonical"
    :robots="$robots"
    :schema-data="$schemaData"
    :social-image="$socialImage"
>
    <div class="section-shell">
        <div class="container-shell">
            <div class="grid gap-8 lg:grid-cols-[240px_1fr]">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-layouts.storefront>
