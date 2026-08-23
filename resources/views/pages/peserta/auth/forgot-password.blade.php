@extends('layouts.app')

@section('title', 'Lupa Kata Sandi | SI-MELAYUR')
@section('hide_dev_nav', true)

@section('content')
    <x-peserta.auth-shell eyebrow="Pemulihan Akun" title="Lupa Kata Sandi" description="Masukkan email akun peserta. Kami akan mengirim tautan aman untuk membuat kata sandi baru.">
        @if (session('status'))
            <div class="mb-5 rounded-2xl border border-teal/20 bg-teal/10 px-4 py-3 text-xs font-medium leading-relaxed text-teal" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('peserta.password.email') }}" class="space-y-5" data-participant-auth-form>
            @csrf
            <div>
                <label for="email" class="mb-2 block text-xs font-semibold text-navy">Email peserta</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required class="w-full rounded-xl border border-border bg-light px-4 py-3.5 text-sm text-navy outline-none placeholder:text-muted-foreground/70 focus:border-ocean focus:ring-4 focus:ring-ocean/10" placeholder="nama@email.com">
                @error('email')<p class="mt-2 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-ocean to-teal px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-ocean/20 transition hover:brightness-110 disabled:cursor-wait disabled:opacity-70" data-participant-submit>
                <span data-participant-submit-label>Kirim Tautan Pemulihan</span><i data-lucide="send" class="h-4 w-4" data-participant-submit-icon aria-hidden="true"></i><span class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/35 border-t-white" data-participant-submit-spinner aria-hidden="true"></span>
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-muted-foreground"><a href="{{ route('peserta.login') }}" class="font-bold text-ocean transition hover:text-teal">Kembali ke halaman masuk</a></p>
    </x-peserta.auth-shell>
@endsection
