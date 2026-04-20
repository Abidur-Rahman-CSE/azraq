<x-layouts.admin
    title="Create Personalization Template | Azraq Bridal"
    page-title="Create Nikah Nama template"
    page-subtitle="Flat certificate editor"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Personalization'],
        ['label' => 'Templates', 'href' => route('admin.personalization.templates.index')],
        ['label' => 'Create'],
    ]"
>
    @include('admin.personalization.templates._form')
</x-layouts.admin>
