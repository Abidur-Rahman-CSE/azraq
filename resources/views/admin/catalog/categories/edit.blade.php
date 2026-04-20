<x-layouts.admin
    title="Edit Category | Azraq Bridal"
    page-title="Edit category"
    page-subtitle="Catalog workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Catalog'],
        ['label' => 'Categories', 'href' => route('admin.catalog.categories.index')],
        ['label' => $category->name],
    ]"
>
    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Category edit"
            :title="'Refine '.$category->name.' across imagery, homepage flags, and SEO.'"
            description="Use this editor to keep category presentation, banners, and related-category structure aligned with the storefront and homepage merchandising system."
        >
            <x-slot:actions>
                <a href="{{ route('admin.catalog.categories.index') }}" class="button-primary">Back to categories</a>
            </x-slot:actions>
        </x-admin.page-header>

        @include('admin.catalog.categories._form')
    </div>
</x-layouts.admin>
