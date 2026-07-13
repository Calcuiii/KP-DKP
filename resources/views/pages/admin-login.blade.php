@extends('layouts.app')

@section('title', 'Masuk Admin - DKP Assistant')

@section('content')
<div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-navy to-ocean px-4 py-10">
    <div class="w-full max-w-sm">
        <div class="rounded-3xl bg-white p-8 shadow-2xl">

            <div class="mb-6 flex justify-center">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-ocean">
                    <i data-lucide="fish" class="h-6 w-6 text-white" aria-hidden="true"></i>
                </div>
            </div>

            <h1 class="mb-1 text-center text-xl font-bold text-navy">Masuk Admin</h1>
            <p class="mb-6 text-center text-xs text-muted-foreground">Panel Manajemen DKP Assistant</p>

            @if (session('status'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-5 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3">
                <i data-lucide="shield" class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-amber-600" aria-hidden="true"></i>
                <p class="text-xs text-amber-700">Halaman ini hanya dapat diakses oleh administrator resmi.</p>
            </div>

            <form method="POST" action="{{ route('admin-login.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-xs font-semibold text-navy">Email</label>
                    <input
                        id="email" type="email" name="email" value="{{ old('email') }}"
                        required autofocus autocomplete="username"
                        class="w-full rounded-xl border border-border bg-input-background px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-primary/30"
                    >
                    @error('email')
                        <p class="mt-1.5 text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-xs font-semibold text-navy">Password</label>
                    <div class="relative">
                        <input
                            id="password" type="password" name="password"
                            required autocomplete="current-password" data-password-input
                            class="w-full rounded-xl border border-border bg-input-background px-3 py-2.5 pr-10 text-sm outline-none focus:ring-2 focus:ring-primary/30"
                        >
                        <button
                            type="button" data-toggle-password aria-label="Tampilkan password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground"
                        >
                            <i data-lucide="eye" data-icon-show class="h-[15px] w-[15px]" aria-hidden="true"></i>
                            <i data-lucide="eye-off" data-icon-hide class="hidden h-[15px] w-[15px]" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex cursor-pointer items-center gap-2 text-xs text-muted-foreground">
                        <input type="checkbox" name="remember" class="rounded">
                        Ingat saya
                    </label>
                    <a href="#" class="text-xs font-medium text-ocean">Lupa password?</a>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-gradient-to-br from-ocean to-navy py-3 text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-95"
                >
                    Masuk
                </button>
            </form>
        </div>
    </div>
</div>
@endsection