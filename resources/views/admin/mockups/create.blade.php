<x-layouts.admin
    title="New Mockup | Azraq Bridal"
    page-title="New mockup"
    page-subtitle="Personalization workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Personalization'],
        ['label' => 'Mockups', 'href' => route('admin.mockups.index')],
        ['label' => 'New mockup'],
    ]"
>
    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Nikah mockup editor"
            title="Create a new mockup with mapped artwork placement."
            description="Upload a base scene, connect it to a personalization template, and define the 4-corner artwork area before this mockup goes live."
        >
            <x-slot:actions>
                <a href="{{ route('admin.mockups.index') }}" class="button-primary">Back to mockups</a>
            </x-slot:actions>
        </x-admin.page-header>

        @include('admin.mockups._form')
    </div>
</x-layouts.admin>
