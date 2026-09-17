@php
    $officialStarted = $application->official_started_at;
    $officialEnded = $application->official_ended_at;
    $today = now()->startOfDay();
    $daysRemaining = $officialEnded ? max(0, $today->diffInDays($officialEnded->copy()->startOfDay(), false)) : null;
    $preparationReminderDate = $officialEnded?->copy()->subDays(10)->startOfDay();
    $isPreparationWindow = $preparationReminderDate
        ? $today->betweenIncluded($preparationReminderDate, $officialEnded->copy()->startOfDay())
        : false;
    $totalDays = $officialStarted && $officialEnded ? max(1, $officialStarted->copy()->startOfDay()->diffInDays($officialEnded->copy()->startOfDay())) : null;
    $elapsedDays = $officialStarted ? max(0, $officialStarted->copy()->startOfDay()->diffInDays($today, false)) : 0;
    $executionProgress = $totalDays ? min(100, max(0, round(($elapsedDays / $totalDays) * 100))) : 0;
    $calendarMonth = $officialStarted && $officialEnded
        ? ($today->betweenIncluded($officialStarted->copy()->startOfDay(), $officialEnded->copy()->startOfDay()) ? $today->copy() : $officialStarted->copy())->startOfMonth()
        : null;
    $replyLetter = $application->replyLetter;
    $phaseLabel = match (true) {
        $application->completed_at !== null => 'Kegiatan selesai',
        $officialEnded !== null && $officialEnded->copy()->endOfDay()->isPast() => 'Penyelesaian administrasi akhir',
        $officialStarted === null => 'Menunggu jadwal pelaksanaan',
        $officialStarted->copy()->startOfDay()->isFuture() => 'Persiapan mulai magang',
        default => 'Magang sedang berlangsung',
    };
@endphp

<section id="ringkasan" class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy via-[#123d72] to-ocean p-7 text-white shadow-xl shadow-navy/15 sm:p-10">
    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full border-[3rem] border-white/[0.06]"></div>
    <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-200/20 bg-teal/15 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-teal-100"><span class="h-2 w-2 rounded-full bg-teal-300"></span>{{ $phaseLabel }}</span>
            <h1 class="mt-5 text-3xl font-bold sm:text-4xl">Selamat menjalankan kegiatan, {{ $participant->name }}!</h1>
            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-blue-100 sm:text-base">Pengajuan Anda telah diterima. Dampingi kegiatan Anda hingga laporan, presentasi, dan penyelesaian administrasi akhir melalui portal ini.</p>
            <a href="#penyelesaian" class="mt-5 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-bold text-navy">Buka panduan penyelesaian →</a>
        </div>
        @if ($officialStarted && $officialEnded)
            <div class="min-w-48 rounded-3xl border border-white/15 bg-white/10 px-6 py-5 backdrop-blur-sm"><p class="text-xs font-bold uppercase tracking-wider text-blue-200">Sisa pelaksanaan</p><p class="mt-1 text-4xl font-bold">{{ $daysRemaining }}</p><p class="text-xs text-blue-100">hari lagi</p></div>
        @endif
    </div>
    @if ($officialStarted && $officialEnded)
        <div class="relative mt-8 border-t border-white/10 pt-6">
            <div class="flex items-center justify-between gap-4 text-xs font-bold text-blue-100"><span>{{ $officialStarted->translatedFormat('d M Y') }}</span><span>{{ $executionProgress }}% periode berjalan</span><span>{{ $officialEnded->translatedFormat('d M Y') }}</span></div>
            <div class="mt-3 h-3 overflow-hidden rounded-full bg-white/15"><div class="h-full rounded-full bg-gradient-to-r from-teal-300 to-white transition-all" style="width: {{ $executionProgress }}%"></div></div>
        </div>
    @endif
</section>

<section id="surat-balasan" class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
    <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-teal/10 text-teal"><i data-lucide="mail-check" class="h-5 w-5"></i></span>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Dokumen resmi</p>
                <h2 class="mt-1 text-xl font-bold">Surat balasan Dinas</h2>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-muted-foreground">
                    @if ($replyLetter)
                        Dokumen balasan resmi dari Dinas sudah tersedia dan dapat diunduh kapan saja.
                    @else
                        Surat balasan resmi belum diunggah oleh Dinas.
                    @endif
                </p>
            </div>
        </div>
        @if ($replyLetter)
            <a href="{{ route('peserta.response-letter.download') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white"><i data-lucide="download" class="h-4 w-4"></i>Unduh Surat Balasan</a>
        @endif
    </div>
</section>

<a href="{{ route('peserta.activities') }}" class="flex items-center justify-between gap-4 rounded-2xl border border-teal/20 bg-white p-6 text-navy shadow-sm transition hover:border-teal">
    <span><span class="block text-lg font-bold">Kegiatan magang Anda</span><span class="mt-1 block text-sm text-muted-foreground">Buka kalender, jadwal resmi, dan tata tertib dalam satu halaman.</span></span>
    <i data-lucide="arrow-up-right" class="h-6 w-6 shrink-0 text-teal"></i>
</a>

@include('components.peserta.completion-guide')

<section id="kenali-si-molek" class="rounded-[2rem] border border-ocean/15 bg-gradient-to-br from-[#edf8ff] via-white to-[#e7f8f5] p-6 shadow-sm sm:p-8">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"><div class="flex items-start gap-4"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-ocean/10 text-ocean"><i data-lucide="info" class="h-5 w-5"></i></span><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Informasi Portal</p><h2 class="mt-1 text-xl font-bold">Butuh melihat panduan SI-MELAYUR?</h2><p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground">Panduan umum tetap tersedia apabila Anda ingin membaca kembali fungsi portal atau membutuhkan bantuan informasi.</p></div></div><a href="{{ route('infographics') }}" class="inline-flex w-fit shrink-0 items-center gap-2 rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white">Buka panduan <i data-lucide="arrow-right" class="h-4 w-4"></i></a></div>
</section>
