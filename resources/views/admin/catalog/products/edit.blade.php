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
        @include('admin.catalog.products._form')
    </div>
</x-layouts.admin>
