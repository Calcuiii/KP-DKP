@extends('layouts.admin')

@section('title', 'Analytics - DKP Assistant')

@section('content')
<div class="space-y-5">

    <form method="GET" class="flex flex-wrap items-center gap-3">
        <select name="period" class="rounded-xl border border-border bg-card px-3 py-2 text-xs shadow-sm" onchange="this.form.submit()">
            <option value="30" @selected($days == 30)>30 Hari Terakhir</option>
            <option value="7" @selected($days == 7)>7 Hari Terakhir</option>
            <option value="90" @selected($days == 90)>90 Hari Terakhir</option>
        </select>

        <select class="rounded-xl border border-border bg-card px-3 py-2 text-xs shadow-sm" disabled>
            <option>Semua Kategori</option>
        </select>

        <a href="{{ route('admin.analytics.export', ['period' => $days]) }}" class="flex items-center gap-1.5 rounded-xl border border-border bg-card px-3 py-2 text-xs shadow-sm hover:bg-accent">
            <i data-lucide="download" class="h-3 w-3" aria-hidden="true"></i>
            Export Laporan
        </a>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.metric-card icon="message-square" label="Total Percakapan" :value="number_format($totalConversations)" color="ocean" />
        <x-admin.metric-card icon="hash" label="Total Pertanyaan" :value="number_format($totalQuestions)" color="teal" />
        <x-admin.metric-card icon="target" label="Answer Rate" :value="$answerRate . '%'" color="indigo" />
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-navy">Pertanyaan dari Waktu ke Waktu</h3>
            <canvas id="questionTrendChart" height="100"></canvas>
        </div>

        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-navy">Kategori Pertanyaan Terbanyak</h3>
            <canvas id="analyticsCategoryChart" height="100"></canvas>
        </div>

        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-navy">Penggunaan Knowledge Base (Top Sumber)</h3>
            @forelse ($kbUsage as $doc)
                <div class="mb-2.5 space-y-1">
                    <div class="flex justify-between text-xs">
                        <span class="max-w-[200px] truncate font-medium text-navy">{{ $doc['name'] }}</span>
                        <span class="text-muted-foreground">{{ $doc['percentage'] }}%</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-[#E4ECF6]">
                        <div class="h-full rounded-full bg-ocean" style="width: {{ $doc['percentage'] }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-muted-foreground">Belum ada dokumen yang terpakai chatbot.</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.analyticsData = {
        questionTrend: @json($questionTrend),
        categoryData: @json($categoryData),
    };
</script>
@endpush
@endsection