<x-layouts.admin
    title="Create Collection | Azraq Bridal"
    page-title="Create collection"
    page-subtitle="Catalog workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Catalog'],
        ['label' => 'Collections', 'href' => route('admin.catalog.collections.index')],
        ['label' => 'Create'],
    ]"
>
    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Collection create"
            title="Create a curated collection with real merchandising data."
            description="This upgraded editor supports cover image, mode, CTA label, featured state, and product assignment so collections can drive homepage and storefront discovery."
        >
            <x-slot:actions>
                <a href="{{ route('admin.catalog.collections.index') }}" class="button-ghost">Back to collections</a>
            </x-slot:actions>
        </x-admin.page-header>

        @include('admin.catalog.collections._form')
    </div>
</x-layouts.admin>
