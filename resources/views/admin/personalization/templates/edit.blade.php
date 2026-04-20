<x-layouts.admin
    title="Edit Personalization Template | Azraq Bridal"
    page-title="Edit Nikah Nama template"
    page-subtitle="Flat certificate editor"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Personalization'],
        ['label' => 'Templates', 'href' => route('admin.personalization.templates.index')],
        ['label' => $template->name],
    ]"
>
    @include('admin.personalization.templates._form')
</x-layouts.admin>
