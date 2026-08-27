@props([
    'officialStarted',
    'officialEnded',
    'today',
    'preparationReminderDate' => null,
    'isPreparationWindow' => false,
])

@php
    $firstMonth = $officialStarted->copy()->startOfMonth();
    $lastMonth = $officialEnded->copy()->startOfMonth();
    $calendarMonths = collect(\Carbon\CarbonPeriod::create($firstMonth, '1 month', $lastMonth))
        ->map(fn ($month) => \Carbon\Carbon::instance($month)->startOfMonth())
        ->values();
    $initialMonth = $today->betweenIncluded($officialStarted->copy()->startOfDay(), $officialEnded->copy()->startOfDay())
        ? $today->copy()->startOfMonth()
        : $firstMonth;
    $initialMonthKey = $initialMonth->format('Y-m');
@endphp

<div data-internship-calendar-slider>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold text-muted-foreground">Kalender pelaksanaan</p>
            <h3 class="mt-1 text-lg font-extrabold" data-calendar-current-label aria-live="polite">{{ $initialMonth->translatedFormat('F Y') }}</h3>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" data-calendar-previous class="flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-white text-ocean transition hover:bg-secondary disabled:cursor-not-allowed disabled:opacity-35" aria-label="Lihat bulan sebelumnya"><i data-lucide="chevron-left" class="h-4 w-4"></i></button>
            <button type="button" data-calendar-next class="flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-white text-ocean transition hover:bg-secondary disabled:cursor-not-allowed disabled:opacity-35" aria-label="Lihat bulan berikutnya"><i data-lucide="chevron-right" class="h-4 w-4"></i></button>
        </div>
    </div>

    <div class="mt-3 flex flex-wrap gap-3 text-[10px] font-bold text-muted-foreground">
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-ocean"></span>Periode magang</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-teal ring-2 ring-teal/20"></span>Hari ini</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-cyan-300"></span>Persiapan laporan &amp; presentasi</span>
    </div>

    @if ($preparationReminderDate)
        <div class="mt-4 flex items-start gap-3 rounded-xl border border-cyan-200 bg-cyan-50 p-3 text-xs leading-relaxed text-ocean">
            <span class="shrink-0 rounded-md bg-cyan-200 px-2 py-1 font-extrabold text-ocean">H-10</span>
            <p><strong>Periode persiapan laporan dan presentasi:</strong> {{ $preparationReminderDate->translatedFormat('d F Y') }}–{{ $officialEnded->copy()->subDay()->translatedFormat('d F Y') }} (H-10 sampai H-1).@if($isPreparationWindow) Periode persiapan sedang berlangsung.@endif</p>
        </div>
    @endif

    @foreach ($calendarMonths as $month)
        @php
            $monthKey = $month->format('Y-m');
            $monthDays = \Carbon\CarbonPeriod::create(
                $month->copy()->startOfWeek(\Carbon\CarbonInterface::MONDAY),
                $month->copy()->endOfMonth()->endOfWeek(\Carbon\CarbonInterface::SUNDAY),
            );
        @endphp
        <div data-calendar-month-panel data-calendar-month-name="{{ $month->translatedFormat('F Y') }}" @if($monthKey !== $initialMonthKey) hidden @endif class="mt-5 grid grid-cols-7 gap-1 text-center">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                <span class="py-2 text-[10px] font-extrabold uppercase tracking-wider text-muted-foreground">{{ $dayName }}</span>
            @endforeach
            @foreach ($monthDays as $calendarDay)
                @php
                    $day = \Carbon\Carbon::instance($calendarDay)->startOfDay();
                    $inCurrentMonth = $day->month === $month->month && $day->year === $month->year;
                    $inInternship = $day->betweenIncluded($officialStarted->copy()->startOfDay(), $officialEnded->copy()->startOfDay());
                    $isToday = $day->isSameDay($today);
                    $isStart = $day->isSameDay($officialStarted);
                    $isEnd = $day->isSameDay($officialEnded);
                    $isPreparationReminder = $preparationReminderDate && $day->isSameDay($preparationReminderDate);
                    $inPreparationWindow = $preparationReminderDate && $day->betweenIncluded(
                        $preparationReminderDate,
                        $officialEnded->copy()->subDay()->startOfDay(),
                    );
                @endphp
                <div @if($inPreparationWindow) data-preparation-window-day @endif class="relative flex aspect-square min-h-9 items-center justify-center rounded-xl text-xs font-bold transition {{ ! $inCurrentMonth ? 'text-slate-300' : ($inInternship ? 'bg-ocean/10 text-ocean' : 'text-navy') }} {{ $inPreparationWindow ? 'bg-cyan-100 text-ocean ring-1 ring-inset ring-cyan-300' : '' }} {{ $isToday ? 'ring-2 ring-teal ring-offset-1' : '' }} {{ ($isStart || $isEnd) ? 'bg-ocean text-white' : '' }}" title="{{ $day->translatedFormat('l, d F Y') }}{{ $isStart ? ' — Hari pertama' : ($isEnd ? ' — Hari terakhir' : ($inPreparationWindow ? ' — Periode persiapan laporan dan presentasi' : '')) }}">
                    {{ $day->day }}
                    @if($isToday)<span class="absolute bottom-1 h-1 w-1 rounded-full bg-teal"></span>@endif
                </div>
            @endforeach
        </div>
    @endforeach
</div>
