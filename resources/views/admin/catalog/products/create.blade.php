<x-layouts.admin
    title="Create Product | Azraq Bridal"
    page-title="Create product"
    page-subtitle="Catalog workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Catalog'],
        ['label' => 'Products', 'href' => route('admin.catalog.products.index')],
        ['label' => 'Create'],
    ]"
>
    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Product create"
            title="Build a media-aware product from the start."
            description="This upgraded editor is organized by catalog, media, product type, SEO, and publish state so new products do not need cleanup immediately after creation."
        >
            <x-slot:actions>
                <a href="{{ route('admin.catalog.products.index') }}" class="button-ghost">Back to products</a>
            </x-slot:actions>
        </x-admin.page-header>

        @include('admin.catalog.products._form')
    </div>
</x-layouts.admin>
