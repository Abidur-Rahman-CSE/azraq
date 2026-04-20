<x-layouts.admin
    title="New Mockup | Azraq Bridal"
    page-title="New mockup"
    page-subtitle="Flat certificate placement"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Personalization'],
        ['label' => 'Mockups', 'href' => route('admin.mockups.index')],
        ['label' => 'New mockup'],
    ]"
>
    @include('admin.mockups._form')
</x-layouts.admin>
