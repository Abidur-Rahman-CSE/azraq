@php($isEdit = $user->exists)

<form method="POST" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="surface-card grid gap-6 p-6 md:grid-cols-2">
        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Name</span>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
            @error('name')
                <small class="text-[var(--color-danger)]">{{ $message }}</small>
            @enderror
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Email</span>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
            @error('email')
                <small class="text-[var(--color-danger)]">{{ $message }}</small>
            @enderror
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Password {{ $isEdit ? '(optional)' : '' }}</span>
            <input type="password" name="password" @required(! $isEdit) autocomplete="new-password" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
            @error('password')
                <small class="text-[var(--color-danger)]">{{ $message }}</small>
            @enderror
        </label>

        <label class="space-y-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Confirm password</span>
            <input type="password" name="password_confirmation" @required(! $isEdit) autocomplete="new-password" class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
        </label>

        <label class="space-y-2 md:col-span-2">
            <span class="text-sm font-medium text-[var(--color-secondary-900)]">User role</span>
            <select name="role" required class="w-full rounded-2xl border border-[var(--color-border-soft)] bg-white px-4 py-3">
                @foreach ($roleOptions as $roleValue => $roleLabel)
                    <option value="{{ $roleValue }}" @selected(old('role', $user->role ?: \App\Models\User::ROLE_CUSTOMER) === $roleValue)>
                        {{ $roleLabel }}
                    </option>
                @endforeach
            </select>
            <small class="block text-[var(--color-text-soft)]">Staff, Manager, Admin, and Super admin roles can access the admin panel. Customer cannot.</small>
            @error('role')
                <small class="text-[var(--color-danger)]">{{ $message }}</small>
            @enderror
        </label>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="button-primary">{{ $isEdit ? 'Save changes' : 'Create user' }}</button>
        <a href="{{ route('admin.users.index') }}" class="button-ghost">Cancel</a>
    </div>
</form>
