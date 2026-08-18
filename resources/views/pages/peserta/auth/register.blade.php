@extends('layouts.app')

@section('title', 'Daftar Akun Peserta | SI-MELAYUR')

@section('hide_dev_nav', true)

@section('content')
    <x-peserta.auth-shell
        eyebrow="Portal Peserta"
        title="Daftar Akun"
        description="Buat akun untuk menyiapkan akses ke layanan pendampingan Magang dan PKL SI-MELAYUR."
    >
        <form method="POST" action="{{ route('peserta.register.store') }}" class="space-y-5" data-participant-auth-form>
            @csrf

            <div>
                <label for="name" class="mb-2 block text-xs font-semibold text-navy">Nama Lengkap</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    autocomplete="name"
                    autofocus
                    required
                    aria-invalid="@error('name') true @else false @enderror"
                    class="w-full rounded-xl border border-border bg-light px-4 py-3.5 text-sm text-navy outline-none placeholder:text-muted-foreground/70 focus:border-ocean focus:ring-4 focus:ring-ocean/10"
                    placeholder="Masukkan nama lengkap"
                >
                @error('name')
                    <p class="mt-2 text-xs font-medium text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="mb-2 block text-xs font-semibold text-navy">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
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
                        autocomplete="new-password"
                        required
                        class="w-full rounded-xl border border-border bg-light px-4 py-3.5 pr-12 text-sm text-navy outline-none placeholder:text-muted-foreground/70 focus:border-ocean focus:ring-4 focus:ring-ocean/10"
                        placeholder="Minimal 8 karakter"
                    >
                    <button type="button" data-participant-password-toggle data-password-target="password" aria-label="Tampilkan kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-muted-foreground transition hover:bg-secondary hover:text-navy">
                        <i data-lucide="eye" data-password-eye-open class="h-4 w-4" aria-hidden="true"></i>
                        <i data-lucide="eye-off" data-password-eye-closed class="hidden h-4 w-4" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-2 text-xs font-medium text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-xs font-semibold text-navy">Konfirmasi Kata Sandi</label>
                <div class="relative">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-xl border border-border bg-light px-4 py-3.5 pr-12 text-sm text-navy outline-none placeholder:text-muted-foreground/70 focus:border-ocean focus:ring-4 focus:ring-ocean/10"
                        placeholder="Ulangi kata sandi"
                    >
                    <button type="button" data-participant-password-toggle data-password-target="password_confirmation" aria-label="Tampilkan kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-muted-foreground transition hover:bg-secondary hover:text-navy">
                        <i data-lucide="eye" data-password-eye-open class="h-4 w-4" aria-hidden="true"></i>
                        <i data-lucide="eye-off" data-password-eye-closed class="hidden h-4 w-4" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-ocean to-teal px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-ocean/20 transition hover:brightness-110 disabled:cursor-wait disabled:opacity-70" data-participant-submit>
                <span data-participant-submit-label>Buat Akun</span>
                <i data-lucide="arrow-right" class="h-4 w-4" data-participant-submit-icon aria-hidden="true"></i>
                <span class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/35 border-t-white" data-participant-submit-spinner aria-hidden="true"></span>
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-muted-foreground">
            Sudah memiliki akun?
            <a href="{{ route('peserta.login') }}" class="font-bold text-ocean transition hover:text-teal">Masuk</a>
        </p>
    </x-peserta.auth-shell>
@endsection
