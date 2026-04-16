<x-layouts.admin title="Personalization Templates | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Personalization</p>
                <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Template manager</h2>
            </div>
            <a href="{{ route('admin.personalization.templates.create') }}" class="button-primary">New template</a>
        </div>

        <div class="surface-card overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-[var(--color-border-soft)] text-[var(--color-text-soft)]">
                    <tr>
                        <th class="px-6 py-4 font-medium">Template</th>
                        <th class="px-6 py-4 font-medium">Product</th>
                        <th class="px-6 py-4 font-medium">Fields</th>
                        <th class="px-6 py-4 font-medium">Fonts</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($templates as $template)
                        <tr class="border-b border-[var(--color-border-soft)]/70 last:border-0">
                            <td class="px-6 py-4">
                                <p class="font-medium text-[var(--color-secondary-900)]">{{ $template->name }}</p>
                                <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $template->proof_note_label ?: 'No proof note label' }}</p>
                            </td>
                            <td class="px-6 py-4">{{ $template->product?->name }}</td>
                            <td class="px-6 py-4">{{ $template->fields()->count() }}</td>
                            <td class="px-6 py-4">{{ $template->fonts()->count() }}</td>
                            <td class="px-6 py-4">{{ $template->is_active ? 'Active' : 'Hidden' }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.personalization.templates.edit', $template) }}" class="text-[var(--color-secondary-900)]">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-[var(--color-text-soft)]">No templates yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $templates->links() }}
    </div>
</x-layouts.admin>
