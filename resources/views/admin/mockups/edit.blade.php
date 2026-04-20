<x-layouts.admin
    title="Edit Mockup | Azraq Bridal"
    page-title="Edit mockup"
    page-subtitle="Personalization workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Personalization'],
        ['label' => 'Mockups', 'href' => route('admin.mockups.index')],
        ['label' => $mockup->title],
    ]"
>
    <div class="space-y-8">
        <x-admin.page-header
            eyebrow="Nikah mockup editor"
            :title="'Refine '.$mockup->title.' and its perspective map.'"
            description="Use the canvas to place the certificate area, then fine-tune the normalized coordinates and supporting render settings on the right."
        >
            <x-slot:actions>
                <a href="{{ route('admin.mockups.index') }}" class="button-ghost">Back to mockups</a>
                <form method="POST" action="{{ route('admin.mockups.duplicate', $mockup) }}">
                    @csrf
                    <button type="submit" class="button-primary">Duplicate mockup</button>
                </form>
            </x-slot:actions>
        </x-admin.page-header>

        @include('admin.mockups._form')
    </div>
</x-layouts.admin>
