@extends('layouts.app')

@section('title', 'Buku Tamu | SI-MELAYUR')
@section('meta_description', 'Verifikasi Buku Tamu sebelum menggunakan asisten informasi SI-MELAYUR.')
@section('hide_dev_nav', 'true')

@section('content')
    <div class="min-h-screen bg-slate-50 font-sans">
        @include('components.landing.navbar')
        <main class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-3xl items-center px-4 py-12 sm:px-6">
            <section class="w-full overflow-hidden rounded-3xl border border-border bg-white shadow-xl shadow-navy/5">
                <div class="bg-gradient-to-br from-navy to-ocean px-7 py-9 text-white sm:px-10">
                    <p class="mb-2 text-sm font-semibold text-blue-100">Check-in Pengunjung · Tanpa akun</p>
                    <h1 class="text-3xl font-bold leading-tight sm:text-4xl">Verifikasi Buku Tamu</h1>
                    <p class="mt-3 text-blue-100">Isi formulir resmi DKP terlebih dahulu untuk membuka asisten informasi.</p>
                </div>
                <div class="px-7 py-8 sm:px-10">
                    @if ($errors->any())
                        <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if (! $guestbookUrl)
                        <div role="status" class="mb-5 rounded-xl border border-border bg-slate-50 p-4 text-sm text-foreground">
                            Tautan Google Form sedang disiapkan oleh pengelola. Verifikasi belum dapat dimulai.
                        </div>
                    @endif

                    @if ($pending && $pending['expires_at'] > now()->timestamp)
                        <h2 class="text-lg font-bold text-navy">Isi formulir, kemudian periksa pengisian</h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                            Gunakan nomor WhatsApp aktif yang berakhir dengan <strong class="text-navy">{{ $pending['phone_suffix'] }}</strong>.
                            Kirim respons baru pada formulir resmi DKP setelah memulai verifikasi ini, lalu kembali ke halaman ini.
                        </p>
                        <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                            @if ($guestbookUrl)
                                <a href="{{ $guestbookUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center justify-center rounded-xl bg-ocean px-5 py-3 font-semibold text-white hover:opacity-90">
                                    Isi Formulir Resmi DKP
                                </a>
                            @endif
                            <form action="{{ route('guestbook.complete') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full rounded-xl border border-ocean px-5 py-3 font-semibold text-ocean hover:bg-slate-50">
                                    Periksa Pengisian
                                </button>
                            </form>
                        </div>
                        <p class="mt-3 text-xs text-muted-foreground">
                            Verifikasi berlaku sampai {{ \Carbon\Carbon::createFromTimestamp($pending['expires_at'])->timezone(config('app.timezone'))->format('H:i') }} WIB.
                            Pengisian lama tidak dihitung. Jika belum ditemukan, tunggu beberapa detik sebelum memeriksa lagi.
                        </p>
                        <details class="mt-6 border-t border-border pt-4">
                            <summary class="cursor-pointer text-sm font-semibold text-ocean">Ganti nomor / mulai ulang</summary>
                            <p class="mt-2 text-xs text-muted-foreground">Memulai ulang berarti Anda perlu mengirim respons baru lagi.</p>
                    @else
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            Masukkan nomor WhatsApp aktif yang akan Anda tulis pada formulir resmi DKP. Sistem memeriksa nomor dan waktu pengisian; Anda tidak perlu membuat akun SI-MELAYUR.
                        </p>
                    @endif

                    <form action="{{ route('guestbook.start') }}" method="POST" class="mt-5">
                        @csrf
                        <label for="guestbook-phone" class="block text-sm font-semibold text-navy">Nomor WhatsApp aktif</label>
                        <input id="guestbook-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel"
                               required maxlength="32" placeholder="Contoh: 081234567890" aria-describedby="phone-help"
                               class="mt-2 w-full rounded-xl border border-border bg-slate-50 px-4 py-3 text-foreground focus:border-ocean focus:outline-none focus:ring-2 focus:ring-ocean/20">
                        <p id="phone-help" class="mt-2 text-xs text-muted-foreground">Gunakan nomor yang sama pada formulir. Format 08, 628, dan +628 didukung.</p>
                        <button type="submit" @disabled(! $guestbookUrl)
                                class="mt-4 rounded-xl bg-ocean px-5 py-3 font-semibold text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                            Mulai Verifikasi
                        </button>
                    </form>

                    @if ($pending && $pending['expires_at'] > now()->timestamp)
                        </details>
                    @endif

                    <p class="mt-6 border-t border-border pt-4 text-xs leading-relaxed text-muted-foreground">
                        Setelah berhasil, akses asisten berlaku maksimal 24 jam pada sesi browser ini.
                        Pencocokan ini bukan verifikasi kepemilikan nomor dan tidak membuka data pribadi atau status pengajuan.
                    </p>
                </div>
            </section>
        </main>
    </div>
@endsection
