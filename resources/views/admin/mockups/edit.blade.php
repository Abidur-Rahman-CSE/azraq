<x-layouts.admin
    title="Edit Mockup | Azraq Bridal"
    page-title="Edit mockup"
    page-subtitle="Flat certificate placement"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Personalization'],
        ['label' => 'Mockups', 'href' => route('admin.mockups.index')],
        ['label' => $mockup->title],
    ]"
>
    @include('admin.mockups._form')
</x-layouts.admin>
