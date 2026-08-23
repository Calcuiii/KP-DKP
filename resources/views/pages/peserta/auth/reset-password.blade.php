@extends('layouts.app')

@section('title', 'Atur Ulang Kata Sandi | SI-MELAYUR')
@section('hide_dev_nav', true)

@section('content')
    <x-peserta.auth-shell eyebrow="Keamanan Akun" title="Buat Kata Sandi Baru" description="Gunakan minimal 8 karakter dan pastikan kata sandi mudah Anda ingat, tetapi sulit ditebak.">
        <form method="POST" action="{{ route('peserta.password.update') }}" class="space-y-5" data-participant-auth-form>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="mb-2 block text-xs font-semibold text-navy">Email peserta</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required class="w-full rounded-xl border border-border bg-light px-4 py-3.5 text-sm text-navy outline-none focus:border-ocean focus:ring-4 focus:ring-ocean/10">
                @error('email')<p class="mt-2 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
            </div>

            @foreach ([['password', 'Kata Sandi Baru', 'new-password'], ['password_confirmation', 'Konfirmasi Kata Sandi', 'new-password']] as [$field, $label, $autocomplete])
                <div>
                    <label for="{{ $field }}" class="mb-2 block text-xs font-semibold text-navy">{{ $label }}</label>
                    <div class="relative">
                        <input id="{{ $field }}" name="{{ $field }}" type="password" autocomplete="{{ $autocomplete }}" required class="w-full rounded-xl border border-border bg-light px-4 py-3.5 pr-12 text-sm text-navy outline-none focus:border-ocean focus:ring-4 focus:ring-ocean/10">
                        <button type="button" data-participant-password-toggle data-password-target="{{ $field }}" aria-label="Tampilkan kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-muted-foreground transition hover:bg-secondary hover:text-navy"><i data-lucide="eye" data-password-eye-open class="h-4 w-4" aria-hidden="true"></i><i data-lucide="eye-off" data-password-eye-closed class="hidden h-4 w-4" aria-hidden="true"></i></button>
                    </div>
                    @error($field)<p class="mt-2 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
                </div>
            @endforeach

            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-ocean to-teal px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-ocean/20 transition hover:brightness-110 disabled:cursor-wait disabled:opacity-70" data-participant-submit><span data-participant-submit-label>Simpan Kata Sandi Baru</span><i data-lucide="shield-check" class="h-4 w-4" data-participant-submit-icon aria-hidden="true"></i><span class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/35 border-t-white" data-participant-submit-spinner aria-hidden="true"></span></button>
        </form>
    </x-peserta.auth-shell>
@endsection
