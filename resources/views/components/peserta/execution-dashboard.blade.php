@php
    $officialStarted = $application->official_started_at;
    $officialEnded = $application->official_ended_at;
    $today = now()->startOfDay();
    $daysRemaining = $officialEnded ? max(0, $today->diffInDays($officialEnded->copy()->startOfDay(), false)) : null;
    $totalDays = $officialStarted && $officialEnded ? max(1, $officialStarted->copy()->startOfDay()->diffInDays($officialEnded->copy()->startOfDay())) : null;
    $elapsedDays = $officialStarted ? max(0, $officialStarted->copy()->startOfDay()->diffInDays($today, false)) : 0;
    $executionProgress = $totalDays ? min(100, max(0, round(($elapsedDays / $totalDays) * 100))) : 0;
    $calendarMonth = $officialStarted && $officialEnded
        ? ($today->betweenIncluded($officialStarted->copy()->startOfDay(), $officialEnded->copy()->startOfDay()) ? $today->copy() : $officialStarted->copy())->startOfMonth()
        : null;
    $calendarDays = $calendarMonth
        ? \Carbon\CarbonPeriod::create($calendarMonth->copy()->startOfWeek(\Carbon\CarbonInterface::MONDAY), $calendarMonth->copy()->endOfMonth()->endOfWeek(\Carbon\CarbonInterface::SUNDAY))
        : collect();
@endphp

<section id="ringkasan" class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy via-[#123d72] to-ocean p-7 text-white shadow-xl shadow-navy/15 sm:p-10">
    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full border-[3rem] border-white/[0.06]"></div>
    <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-200/20 bg-teal/15 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.18em] text-teal-100"><span class="h-2 w-2 animate-pulse rounded-full bg-teal-300"></span>Magang sedang berlangsung</span>
            <h1 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Selamat menjalankan kegiatan, {{ $participant->name }}!</h1>
            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-blue-100 sm:text-base">Pengajuan Anda telah diterima. Gunakan dashboard ini untuk memantau periode pelaksanaan dan sisa waktu kegiatan.</p>
        </div>
        @if ($officialStarted && $officialEnded)
            <div class="min-w-48 rounded-3xl border border-white/15 bg-white/10 px-6 py-5 backdrop-blur-sm"><p class="text-xs font-bold uppercase tracking-wider text-blue-200">Sisa pelaksanaan</p><p class="mt-1 text-4xl font-extrabold">{{ $daysRemaining }}</p><p class="text-xs text-blue-100">hari lagi</p></div>
        @endif
    </div>
    @if ($officialStarted && $officialEnded)
        <div class="relative mt-8 border-t border-white/10 pt-6">
            <div class="flex items-center justify-between gap-4 text-xs font-bold text-blue-100"><span>{{ $officialStarted->translatedFormat('d M Y') }}</span><span>{{ $executionProgress }}% periode berjalan</span><span>{{ $officialEnded->translatedFormat('d M Y') }}</span></div>
            <div class="mt-3 h-3 overflow-hidden rounded-full bg-white/15"><div class="h-full rounded-full bg-gradient-to-r from-teal-300 to-white transition-all" style="width: {{ $executionProgress }}%"></div></div>
        </div>
    @endif
</section>

<section id="kalender-kegiatan" class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-ocean to-teal text-white"><i data-lucide="calendar-range" class="h-5 w-5"></i></span><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Ruang pelaksanaan</p><h2 class="mt-1 text-2xl font-extrabold">Kalender kegiatan magang</h2><p class="mt-2 text-sm text-muted-foreground">Pantau hari pertama, hari ini, dan batas akhir kegiatan Anda.</p></div></div>
        @if ($calendarMonth)<button type="button" data-internship-calendar-toggle aria-expanded="true" aria-controls="execution-calendar-detail" class="inline-flex w-fit items-center gap-2 rounded-xl bg-navy px-5 py-3 text-sm font-extrabold text-white"><span data-calendar-toggle-label>Tutup kalender</span><i data-lucide="chevron-down" data-calendar-toggle-icon class="h-4 w-4 rotate-180 transition-transform"></i></button>@endif
    </div>

    @if ($calendarMonth)
        <div id="execution-calendar-detail" data-internship-calendar-detail class="mt-7 grid gap-6 xl:grid-cols-[0.32fr_0.68fr]">
            <div class="rounded-2xl bg-light p-5">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-teal">Jadwal resmi</p>
                <div class="mt-4 space-y-3"><div class="rounded-xl bg-white p-4"><p class="text-[10px] font-bold uppercase text-muted-foreground">Mulai</p><p class="mt-1 text-sm font-extrabold">{{ $officialStarted->translatedFormat('l, d F Y') }}</p></div><div class="rounded-xl bg-white p-4"><p class="text-[10px] font-bold uppercase text-muted-foreground">Selesai</p><p class="mt-1 text-sm font-extrabold">{{ $officialEnded->translatedFormat('l, d F Y') }}</p></div></div>
                <p class="mt-4 text-xs leading-relaxed text-muted-foreground">Perubahan periode hanya dapat ditetapkan oleh admin Dinas.</p>
            </div>
            <div class="rounded-2xl border border-border p-4 sm:p-5">
                <div class="flex items-center justify-between gap-4"><div><p class="text-xs text-muted-foreground">Kalender pelaksanaan</p><h3 class="mt-1 text-lg font-extrabold">{{ $calendarMonth->translatedFormat('F Y') }}</h3></div><div class="hidden gap-3 text-[10px] font-bold text-muted-foreground sm:flex"><span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-ocean"></span>Periode</span><span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-teal"></span>Hari ini</span></div></div>
                <div class="mt-5 grid grid-cols-7 gap-1 text-center">
                    @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)<span class="py-2 text-[10px] font-extrabold uppercase text-muted-foreground">{{ $dayName }}</span>@endforeach
                    @foreach ($calendarDays as $calendarDay)
                        @php
                            $day = \Carbon\Carbon::instance($calendarDay)->startOfDay();
                            $inCurrentMonth = $day->month === $calendarMonth->month;
                            $inInternship = $day->betweenIncluded($officialStarted->copy()->startOfDay(), $officialEnded->copy()->startOfDay());
                            $isToday = $day->isSameDay($today);
                            $isBoundary = $day->isSameDay($officialStarted) || $day->isSameDay($officialEnded);
                        @endphp
                        <div class="relative flex aspect-square min-h-9 items-center justify-center rounded-xl text-xs font-bold {{ ! $inCurrentMonth ? 'text-slate-300' : ($inInternship ? 'bg-ocean/10 text-ocean' : 'text-navy') }} {{ $isToday ? 'ring-2 ring-teal ring-offset-1' : '' }} {{ $isBoundary ? 'bg-ocean text-white' : '' }}">{{ $day->day }}@if($isToday)<span class="absolute bottom-1 h-1 w-1 rounded-full bg-teal"></span>@endif</div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="mt-6 rounded-2xl bg-amber-50 p-5 text-sm font-semibold text-amber-800">Menunggu admin menetapkan tanggal mulai dan selesai resmi.</div>
    @endif
</section>

<section id="progress" class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
    <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Riwayat pengajuan</p><h2 class="mt-2 text-2xl font-extrabold">Tahap persiapan telah selesai</h2><p class="mt-2 text-sm text-muted-foreground">Seluruh proses sebelum pelaksanaan diringkas di bawah ini.</p></div>
    <ol class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach (array_slice($sidebarProgress, 0, 5) as $index => $progressItem)
            <li class="rounded-2xl border border-teal/20 bg-teal/[0.05] p-4"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal text-white"><i data-lucide="check" class="h-4 w-4"></i></span><p class="mt-4 text-sm font-extrabold">{{ $progressItem['label'] }}</p><p class="mt-1 text-[11px] font-semibold text-teal">Selesai</p></li>
        @endforeach
    </ol>
</section>

<section id="kenali-si-molek" class="rounded-[2rem] border border-ocean/15 bg-gradient-to-br from-[#edf8ff] via-white to-[#e7f8f5] p-6 shadow-sm sm:p-8">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"><div class="flex items-start gap-4"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-ocean/10 text-ocean"><i data-lucide="info" class="h-5 w-5"></i></span><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Informasi Portal</p><h2 class="mt-1 text-xl font-extrabold">Butuh melihat panduan SI-MELAYUR?</h2><p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground">Panduan umum tetap tersedia apabila Anda ingin membaca kembali fungsi portal atau membutuhkan bantuan informasi.</p></div></div><a href="{{ route('landing') }}" class="inline-flex w-fit shrink-0 items-center gap-2 rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white">Buka panduan <i data-lucide="arrow-right" class="h-4 w-4"></i></a></div>
</section>
