<x-layouts.admin title="Tags | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Catalog</p>
                <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Tags</h2>
            </div>
            <a href="{{ route('admin.catalog.tags.create') }}" class="button-primary">New tag</a>
        </div>

        <div class="surface-card overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-[var(--color-border-soft)] text-[var(--color-text-soft)]">
                    <tr>
                        <th class="px-6 py-4 font-medium">Name</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tags as $tag)
                        <tr class="border-b border-[var(--color-border-soft)]/70 last:border-0">
                            <td class="px-6 py-4">
                                <p class="font-medium text-[var(--color-secondary-900)]">{{ $tag->name }}</p>
                                <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $tag->slug }}</p>
                            </td>
                            <td class="px-6 py-4">{{ $tag->is_active ? 'Active' : 'Hidden' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-3">
                                    <a href="{{ route('admin.catalog.tags.edit', $tag) }}" class="text-[var(--color-secondary-900)]">Edit</a>
                                    <form method="POST" action="{{ route('admin.catalog.tags.destroy', $tag) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[var(--color-danger)]">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-[var(--color-text-soft)]">No tags yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $tags->links() }}
    </div>
</x-layouts.admin>
