<x-layouts.admin
    title="Edit Product | Azraq Bridal"
    page-title="Edit product"
    page-subtitle="Catalog workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Catalog'],
        ['label' => 'Products', 'href' => route('admin.catalog.products.index')],
        ['label' => $product->name],
    ]"
>
    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Product edit"
            :title="'Refine '.$product->name.' across media, type logic, and storefront readiness.'"
            description="Use this editor to keep featured imagery, gallery ordering, personalization support, and operational settings in sync without bouncing between separate pages."
        >
            <x-slot:actions>
                <a href="{{ route('products.show', $product) }}" class="button-ghost" target="_blank" rel="noreferrer">View storefront</a>
                <a href="{{ route('admin.catalog.products.index') }}" class="button-primary">Back to products</a>
            </x-slot:actions>
        </x-admin.page-header>

        @include('admin.catalog.products._form')
    </div>
</x-layouts.admin>
