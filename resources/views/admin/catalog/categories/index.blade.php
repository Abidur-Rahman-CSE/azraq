<x-layouts.admin title="Categories | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Catalog</p>
                <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Categories</h2>
            </div>
            <a href="{{ route('admin.catalog.categories.create') }}" class="button-primary">New category</a>
        </div>

        <div class="surface-card overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-[var(--color-border-soft)] text-[var(--color-text-soft)]">
                    <tr>
                        <th class="px-6 py-4 font-medium">Name</th>
                        <th class="px-6 py-4 font-medium">Parent</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium">Sort</th>
                        <th class="px-6 py-4 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr class="border-b border-[var(--color-border-soft)]/70 last:border-0">
                            <td class="px-6 py-4">
                                <p class="font-medium text-[var(--color-secondary-900)]">{{ $category->name }}</p>
                                <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $category->slug }}</p>
                            </td>
                            <td class="px-6 py-4 text-[var(--color-text-soft)]">{{ $category->parent?->name ?? 'Primary' }}</td>
                            <td class="px-6 py-4">{{ $category->is_active ? 'Active' : 'Hidden' }}</td>
                            <td class="px-6 py-4">{{ $category->sort_order }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-3">
                                    <a href="{{ route('admin.catalog.categories.edit', $category) }}" class="text-[var(--color-secondary-900)]">Edit</a>
                                    <form method="POST" action="{{ route('admin.catalog.categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[var(--color-danger)]">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-[var(--color-text-soft)]">No categories yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $categories->links() }}
    </div>
</x-layouts.admin>
