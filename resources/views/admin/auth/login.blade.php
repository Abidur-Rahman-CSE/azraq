<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Login | {{ config('brand.name') }}</title>
        <meta name="robots" content="noindex,nofollow">
        @include('components.layouts._favicons')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[var(--color-surface)] font-sans text-[var(--color-text-main)] antialiased">
        <main class="grid min-h-screen place-items-center px-4 py-10">
            <section class="w-full max-w-md rounded-[1.5rem] border border-[var(--color-border)] bg-white p-8 shadow-[var(--shadow-soft)]">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full border border-[var(--color-border)] bg-[var(--color-surface-soft)]">
                        <img src="{{ asset('images/logo/Azraq.svg') }}" alt="Azraq Bridal" class="h-7 w-7">
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-primary-900)]">Azraq Bridal</p>
                        <h1 class="text-2xl font-semibold text-[var(--color-secondary-900)]">Admin login</h1>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <label class="nikah-field">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                        @error('email')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="nikah-field">
                        <span>Password</span>
                        <input type="password" name="password" required autocomplete="current-password">
                        @error('password')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="general-checkbox">
                        <input type="checkbox" name="remember" value="1">
                        <span>Remember this device</span>
                    </label>

                    <button type="submit" class="button-primary w-full justify-center">Sign in</button>
                </form>
            </section>
        </main>
    </body>
</html>
