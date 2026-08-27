@extends('layouts.app')

@section('title', 'Masuk Peserta | SI-MELAYUR')

@section('hide_dev_nav', true)

@section('content')
    <x-peserta.auth-shell
        eyebrow="Portal Peserta"
        title="Masuk"
        description="Masuk untuk melanjutkan perjalanan layanan Magang, PKL, dan WOPPS SI-MELAYUR."
    >
        <form method="POST" action="{{ route('peserta.login.store') }}" class="space-y-5" data-participant-auth-form>
            @csrf

            <div>
                <label for="email" class="mb-2 block text-xs font-semibold text-navy">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    autofocus
                    required
                    aria-invalid="@error('email') true @else false @enderror"
                    class="w-full rounded-xl border border-border bg-light px-4 py-3.5 text-sm text-navy outline-none placeholder:text-muted-foreground/70 focus:border-ocean focus:ring-4 focus:ring-ocean/10"
                    placeholder="nama@email.com"
                >
                @error('email')
                    <p class="mt-2 text-xs font-medium text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-xs font-semibold text-navy">Kata Sandi</label>
                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full rounded-xl border border-border bg-light px-4 py-3.5 pr-12 text-sm text-navy outline-none placeholder:text-muted-foreground/70 focus:border-ocean focus:ring-4 focus:ring-ocean/10"
                        placeholder="Masukkan kata sandi"
                    >
                    <button type="button" data-participant-password-toggle data-password-target="password" aria-label="Tampilkan kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-muted-foreground transition hover:bg-secondary hover:text-navy">
                        <i data-lucide="eye" data-password-eye-open class="h-4 w-4" aria-hidden="true"></i>
                        <i data-lucide="eye-off" data-password-eye-closed class="hidden h-4 w-4" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('peserta.password.request') }}" class="text-xs font-semibold text-teal transition hover:text-ocean hover:underline">Lupa Kata Sandi?</a>
            </div>

            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-ocean to-teal px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-ocean/20 transition hover:brightness-110 disabled:cursor-wait disabled:opacity-70" data-participant-submit>
                <span data-participant-submit-label>Masuk</span>
                <i data-lucide="arrow-right" class="h-4 w-4" data-participant-submit-icon aria-hidden="true"></i>
                <span class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/35 border-t-white" data-participant-submit-spinner aria-hidden="true"></span>
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-muted-foreground">
            Belum memiliki akun?
            <a href="{{ route('peserta.register') }}" class="font-bold text-ocean transition hover:text-teal">Daftar Akun</a>
        </p>
    </x-peserta.auth-shell>
@endsection
