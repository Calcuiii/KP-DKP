@extends('layouts.admin')

@section('title', 'Dashboard - DKP Assistant')

@section('content')
<div class="space-y-6">

    {{-- Metric Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($metrics as $m)
            <x-admin.metric-card
                :icon="$m['icon']" :label="$m['label']"
                :value="$m['value']" :sub="$m['sub']" :color="$m['color']"
            />
        @endforeach
    </div>

    {{-- Trend chart + status pie --}}
    <div class="grid gap-5 lg:grid-cols-3">
        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm lg:col-span-2">
            <h3 class="mb-4 text-sm font-semibold text-navy">Tren Pertanyaan Chatbot (30 Hari)</h3>
            <canvas id="trendChart" height="90"></canvas>
        </div>

        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-navy">Status Jawaban</h3>
            <canvas id="statusChart" height="140"></canvas>
            <div class="mt-3 space-y-1.5">
                @foreach ($statusData as $s)
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full" style="background:{{ $s['color'] }}"></span>
                            {{ $s['name'] }}
                        </div>
                        <span class="font-semibold">{{ $s['value'] }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Category bar + unanswered list --}}
    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-navy">Kategori Pertanyaan Terbanyak</h3>
            <canvas id="categoryChart" height="120"></canvas>
        </div>

        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-navy">Pertanyaan Tidak Terjawab Terbaru</h3>
                <a href="#" class="text-xs font-medium text-ocean">Lihat Semua →</a>
            </div>
            <div class="space-y-2.5">
                @foreach ($unanswered as $u)
                    <div class="flex items-start gap-2 rounded-xl border border-border bg-[#F4F7FB] p-2.5">
                        <i data-lucide="alert-circle" class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-amber-500" aria-hidden="true"></i>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-medium text-navy">{{ $u['question'] }}</p>
                            <p class="mt-0.5 flex items-center gap-1 text-[10px] text-muted-foreground">
                                Ditanyakan {{ $u['freq'] }}x · <x-admin.status-badge :status="$u['status']" />
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recent questions table --}}
    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        <div class="border-b border-border px-5 py-4">
            <h3 class="text-sm font-semibold text-navy">Pertanyaan Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-[#F4F7FB] text-muted-foreground">
                        <th class="px-4 py-3 text-left font-semibold">Pertanyaan</th>
                        <th class="px-4 py-3 text-left font-semibold">Kategori</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentQuestions as $row)
                        <tr class="border-t border-border transition-colors hover:bg-[#F8FAFC]">
                            <td class="max-w-[220px] truncate px-4 py-3">{{ $row['question'] }}</td>
                            <td class="px-4 py-3"><x-admin.badge>{{ $row['category'] }}</x-admin.badge></td>
                            <td class="px-4 py-3"><x-admin.status-badge :status="$row['status']" /></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $row['time'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.dashboardData = {
        trend: @json($trend),
        statusData: @json($statusData),
        categoryData: @json($categoryData),
    };
</script>
@endpush
@endsection