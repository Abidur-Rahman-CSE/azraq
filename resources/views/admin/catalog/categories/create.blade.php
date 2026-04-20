<x-layouts.admin
    title="Create Category | Azraq Bridal"
    page-title="Create category"
    page-subtitle="Catalog workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Catalog'],
        ['label' => 'Categories', 'href' => route('admin.catalog.categories.index')],
        ['label' => 'Create'],
    ]"
>
    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Category create"
            title="Create a category with real media coverage."
            description="This upgraded category editor includes image, banner, homepage, and SEO fields so categories can drive both browse flows and homepage content without later cleanup."
        >
            <x-slot:actions>
                <a href="{{ route('admin.catalog.categories.index') }}" class="button-ghost">Back to categories</a>
            </x-slot:actions>
        </x-admin.page-header>

        @include('admin.catalog.categories._form')
    </div>
</x-layouts.admin>
