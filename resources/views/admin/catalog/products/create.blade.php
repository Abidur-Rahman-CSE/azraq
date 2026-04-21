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
        @include('admin.catalog.products._form')
    </div>
</x-layouts.admin>
