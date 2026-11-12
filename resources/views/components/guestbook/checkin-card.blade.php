@php
    $hasPending = $pending && $pending['expires_at'] > now()->timestamp;
@endphp
<section aria-labelledby="checkin-title" class="rounded-2xl border border-border bg-white p-6 shadow-sm sm:p-8">
    <header class="mb-7">
        <p class="mb-3 text-xs font-semibold tracking-wide text-ocean">Magang / KP / PKL</p>
        <h1 id="checkin-title" class="text-2xl font-bold tracking-tight text-navy sm:text-3xl">Verifikasi Buku Tamu</h1>
        <p class="mt-3 text-sm leading-relaxed text-muted-foreground">Gunakan nomor WhatsApp yang Anda tulis saat mengisi Buku Tamu.</p>
    </header>

    @if ($errors->any())
        <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm leading-relaxed text-red-800">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (! $guestbookUrl)
        <p role="status" class="mb-5 text-sm leading-relaxed text-muted-foreground">Tautan Google Form sedang disiapkan oleh pengelola. Verifikasi belum dapat dimulai.</p>
    @endif

    @if ($hasPending)
        <form action="{{ route('guestbook.complete') }}" method="POST">
            @csrf
            <p class="text-sm font-semibold text-navy">Nomor WhatsApp aktif</p>
            <p class="mt-2 text-lg font-semibold tracking-widest text-navy" aria-label="Nomor berakhir dengan {{ $pending['phone_suffix'] }}">•••• •••• {{ $pending['phone_suffix'] }}</p>
            <button type="submit" @disabled(! $guestbookUrl)
                    class="mt-5 w-full rounded-xl bg-ocean px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-navy focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ocean focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                Verifikasi nomor
            </button>
        </form>
        <details class="mt-4 text-sm">
            <summary class="w-fit cursor-pointer rounded font-semibold text-ocean focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ocean">Ganti nomor</summary>
            @include('components.guestbook.phone-form')
        </details>
    @else
        @include('components.guestbook.phone-form')
    @endif

    @if ($guestbookUrl)
        <p class="mt-6 text-center text-sm leading-relaxed text-muted-foreground">
            Belum mengisi?
            <a href="{{ $guestbookUrl }}" target="_blank" rel="noopener noreferrer"
               class="rounded font-semibold text-ocean underline underline-offset-4 hover:text-navy focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ocean">
                Isi Buku Tamu <span aria-hidden="true">↗</span><span class="sr-only"> (tab baru)</span>
            </a>
        </p>
    @endif

    <details class="mt-6 border-t border-border pt-4 text-xs text-muted-foreground">
        <summary class="w-fit cursor-pointer rounded font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ocean">Informasi verifikasi</summary>
        <div class="mt-3 space-y-2 leading-relaxed">
            <p>Sudah pernah mengisi? Anda tidak perlu mengisi Buku Tamu kembali. Jika baru saja mengisi, tunggu beberapa detik sebelum memeriksa.</p>
            @if ($hasPending)
                <p>Periksa nomor sebelum {{ \Carbon\Carbon::createFromTimestamp($pending['expires_at'])->timezone(config('app.timezone'))->format('H:i') }} WIB. Jika sesi berakhir, masukkan nomor kembali.</p>
            @endif
            <p>Setelah berhasil, akses asisten berlaku maksimal 24 jam pada sesi browser ini. Pencocokan ini bukan verifikasi kepemilikan nomor dan tidak membuka data pribadi atau status pengajuan.</p>
        </div>
    </details>
</section>
