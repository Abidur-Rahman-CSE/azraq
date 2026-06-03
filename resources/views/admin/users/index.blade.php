<x-layouts.admin title="Users | Azraq Bridal">
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Operations</p>
                <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Users</h2>
                <p class="mt-2 text-sm text-[var(--color-text-soft)]">Manage admin access and internal user accounts.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="button-primary">New user</a>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="surface-card flex flex-wrap items-end gap-3 p-4">
            <label class="min-w-[260px] flex-1 space-y-2">
                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Search users</span>
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Name or email"
                    class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3"
                >
            </label>
            <button type="submit" class="button-ghost">Search</button>
            @if ($search !== '')
                <a href="{{ route('admin.users.index') }}" class="button-ghost">Clear</a>
            @endif
        </form>

        <div class="surface-card overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-[var(--color-border-soft)] text-[var(--color-text-soft)]">
                    <tr>
                        <th class="px-6 py-4 font-medium">User</th>
                        <th class="px-6 py-4 font-medium">Role</th>
                        <th class="px-6 py-4 font-medium">Created</th>
                        <th class="px-6 py-4 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-b border-[var(--color-border-soft)]/70 last:border-0">
                            <td class="px-6 py-4">
                                <p class="font-medium text-[var(--color-secondary-900)]">
                                    {{ $user->name }}
                                    @if ($user->is(auth()->user()))
                                        <span class="ml-2 rounded-full bg-[var(--color-surface-soft)] px-2 py-0.5 text-[10px] uppercase tracking-[0.14em] text-[var(--color-text-soft)]">You</span>
                                    @endif
                                </p>
                                <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $user->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-[rgba(120,0,0,0.08)] text-[var(--color-primary-900)]' => $user->isAdmin(),
                                    'bg-[var(--color-surface-soft)] text-[var(--color-text-soft)]' => ! $user->isAdmin(),
                                ])>
                                    {{ $user->roleLabel() }}
                                </span>
                                <p class="mt-2 text-xs text-[var(--color-text-soft)]">{{ $user->isAdmin() ? 'Admin panel access' : 'No admin access' }}</p>
                            </td>
                            <td class="px-6 py-4 text-[var(--color-text-soft)]">{{ $user->created_at?->format('M j, Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-[var(--color-secondary-900)]">Edit</a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[var(--color-danger)]" @disabled($user->is(auth()->user()))>Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-[var(--color-text-soft)]">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
</x-layouts.admin>
