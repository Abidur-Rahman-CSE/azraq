<x-layouts.admin
    title="Edit Collection | Azraq Bridal"
    page-title="Edit collection"
    page-subtitle="Catalog workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Catalog'],
        ['label' => 'Collections', 'href' => route('admin.catalog.collections.index')],
        ['label' => $collection->name],
    ]"
>
    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Collection edit"
            :title="'Refine '.$collection->name.' across cover media and product assignment.'"
            description="Use this editor to keep collection covers, CTA messaging, mode, featured state, and assigned product sets aligned with the storefront merchandising system."
        >
            <x-slot:actions>
                <a href="{{ route('admin.catalog.collections.index') }}" class="button-primary">Back to collections</a>
            </x-slot:actions>
        </x-admin.page-header>

        @include('admin.catalog.collections._form')
    </div>
</x-layouts.admin>
