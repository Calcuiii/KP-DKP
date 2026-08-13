@extends('layouts.app')

@section('title', 'Verifikasi Email | Si-Molek')

@section('hide_dev_nav', true)

@section('content')
    <x-peserta.auth-shell
        eyebrow="Portal Peserta"
        title="Verifikasi Email"
        description="Satu langkah lagi untuk mengamankan akun Anda dan menerima pembaruan layanan melalui email."
    >
        @if (auth('peserta')->user()->hasVerifiedEmail())
            <div class="rounded-2xl border border-teal/25 bg-teal/[0.08] p-5">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal text-white">
                        <i data-lucide="badge-check" class="h-5 w-5" aria-hidden="true"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-navy">Email Anda sudah terverifikasi</h2>
                        <p class="mt-1 text-sm leading-relaxed text-muted-foreground">
                            Akun <span class="font-semibold text-navy">{{ auth('peserta')->user()->email }}</span> sudah siap digunakan untuk layanan Portal Peserta.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <a href="{{ route('peserta.dashboard') }}" class="flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-ocean to-teal px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-ocean/20 transition hover:brightness-110">
                    Buka Portal Peserta
                    <i data-lucide="arrow-right" class="h-4 w-4" aria-hidden="true"></i>
                </a>
                <a href="{{ route('landing') }}" class="flex items-center justify-center rounded-full border border-border px-5 py-3.5 text-sm font-bold text-navy transition hover:bg-light">
                    Kembali ke Beranda
                </a>
            </div>
        @else
            <div class="rounded-2xl border border-ocean/15 bg-secondary/55 p-5">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ocean text-white">
                        <i data-lucide="mail-check" class="h-5 w-5" aria-hidden="true"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-navy">Periksa kotak masuk Anda</h2>
                        <p class="mt-1 text-sm leading-relaxed text-muted-foreground">
                            Kami telah mengirim tautan verifikasi ke <span class="font-semibold text-navy">{{ auth('peserta')->user()->email }}</span>.
                            Buka tautan tersebut untuk mengaktifkan akun Anda.
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('verification.send') }}" class="mt-6" data-participant-auth-form>
                @csrf

                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-ocean to-teal px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-ocean/20 transition hover:brightness-110 disabled:cursor-wait disabled:opacity-70" data-participant-submit>
                    <span data-participant-submit-label>Kirim Ulang Email Verifikasi</span>
                    <i data-lucide="send" class="h-4 w-4" data-participant-submit-icon aria-hidden="true"></i>
                    <span class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/35 border-t-white" data-participant-submit-spinner aria-hidden="true"></span>
                </button>
            </form>

            <form method="POST" action="{{ route('peserta.logout') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" class="text-sm font-semibold text-muted-foreground transition hover:text-navy">Masuk dengan akun lain</button>
            </form>
        @endif
    </x-peserta.auth-shell>
@endsection
