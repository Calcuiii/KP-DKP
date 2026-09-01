@extends('layouts.admin')

@section('title', 'Dashboard - SI-MELAYUR')

@section('content')
<div class="space-y-4">
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-navy via-ocean to-[#2875be] px-5 py-4 text-white shadow-sm sm:px-7">
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-3">
            <div class="max-w-xl">
                <h2 class="text-lg font-bold sm:text-xl">Selamat datang, {{ auth()->user()->name }}</h2>
                <p class="mt-1 text-xs leading-relaxed text-blue-100">Pantau aktivitas chatbot dan portal peserta magang/PKL serta WOPPS dari satu tempat.</p>
            </div>
            <div class="relative z-10 flex flex-wrap gap-2">
                <a href="{{ route('admin.conversation-logs') }}" class="rounded-xl bg-white/15 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/25">Log Percakapan</a>
                <a href="{{ route('admin.pemeriksaan-dokumen') }}" class="rounded-xl border border-white/35 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/10">Pemeriksaan Dokumen</a>
            </div>
        </div>
        <div class="absolute -right-12 -top-16 h-40 w-40 rounded-full border-[24px] border-white/10"></div>
    </section>

    {{-- ── Chatbot ─────────────────────────────────────────────── --}}
    <div>
        <p class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.14em] text-sky-600">
            <span class="h-2 w-2 rounded-full bg-sky-500"></span>Layanan Chatbot
        </p>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $m)
                <x-admin.metric-card
                    :icon="$m['icon']" :label="$m['label']"
                    :value="$m['value']" :sub="$m['sub']" :color="$m['color']"
                />
            @endforeach
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-12">
        <div class="rounded-2xl border border-border bg-card p-4 shadow-sm xl:col-span-7">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-500"><i data-lucide="trending-up" class="h-3.5 w-3.5" aria-hidden="true"></i></span>
                    <div>
                        <p class="text-xs font-semibold text-navy">Tren Pertanyaan Chatbot</p>
                        <p class="text-[10px] text-muted-foreground">30 hari terakhir</p>
                    </div>
                </div>
                <a href="{{ route('admin.analytics') }}" class="text-xs font-semibold text-ocean">Analytics →</a>
            </div>
            <div style="position:relative; height:150px; margin-top:8px;">
                <canvas id="trendChart"></canvas>
            </div>
            <div class="mt-2 flex items-center gap-4 border-t border-border pt-2">
                @foreach ($statusData as $s)
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="h-2 w-2 shrink-0 rounded-full" style="background:{{ $s['color'] }}"></span>
                        {{ $s['name'] }}
                        <span class="font-semibold text-navy">{{ $s['value'] }}%</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col gap-3 xl:col-span-5">
            <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-4 shadow-sm">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="flex items-center gap-2.5 text-xs font-semibold text-navy">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600"><i data-lucide="alert-circle" class="h-3.5 w-3.5" aria-hidden="true"></i></span>
                        Perlu Ditinjau
                    </h3>
                    <a href="{{ route('admin.unanswered-questions') }}" class="text-xs font-semibold text-ocean">Lihat Semua →</a>
                </div>
                <div class="space-y-1.5">
                    @forelse ($unansweredList->take(2) as $u)
                        <div class="flex items-start gap-2 rounded-lg border border-amber-200/70 bg-white p-2">
                            <i data-lucide="alert-circle" class="mt-0.5 h-3 w-3 flex-shrink-0 text-amber-500" aria-hidden="true"></i>
                            <div class="min-w-0">
                                <p class="truncate text-[11px] font-medium text-navy">{{ $u['question'] }}</p>
                                <p class="text-[10px] text-muted-foreground">{{ $u['time'] }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-amber-200 bg-white p-2.5 text-[11px] text-muted-foreground">Belum ada pertanyaan yang perlu ditinjau.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-border bg-card p-4 shadow-sm">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="flex items-center gap-2.5 text-xs font-semibold text-navy">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-500"><i data-lucide="message-square" class="h-3.5 w-3.5" aria-hidden="true"></i></span>
                        Pertanyaan Terbaru
                    </h3>
                    <a href="{{ route('admin.conversation-logs') }}" class="text-xs font-semibold text-ocean">Buka Log →</a>
                </div>
                <div class="space-y-1.5">
                    @forelse ($recentQuestions->take(2) as $row)
                        <div class="flex items-center justify-between gap-2 rounded-lg bg-[#F4F7FB] px-2.5 py-2 text-[11px]">
                            <p class="min-w-0 flex-1 truncate">{{ $row['question'] }}</p>
                            <x-admin.status-badge :status="$row['status']" />
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-border bg-white p-2.5 text-[11px] text-muted-foreground">Belum ada percakapan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Portal Peserta ──────────────────────────────────────── --}}
    <div>
        <p class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.14em] text-emerald-600">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>Portal Peserta · Magang / PKL &amp; WOPPS
        </p>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($portalMetrics as $m)
                <x-admin.metric-card
                    :icon="$m['icon']" :label="$m['label']"
                    :value="$m['value']" :sub="$m['sub']" :color="$m['color']"
                />
            @endforeach
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-12">
        <section class="rounded-2xl border border-rose-200 bg-rose-50/40 p-4 shadow-sm xl:col-span-5">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600"><i data-lucide="file-clock" class="h-4 w-4" aria-hidden="true"></i></span>
                    <div>
                        <h3 class="text-sm font-semibold text-navy">Dokumen Menunggu Terlama</h3>
                        <p class="mt-0.5 text-xs text-muted-foreground">Perlu segera ditinjau agar tidak menumpuk.</p>
                    </div>
                </div>
                <a href="{{ route('admin.pemeriksaan-dokumen') }}" class="text-xs font-semibold text-ocean">Lihat Semua →</a>
            </div>
            @forelse ($pendingDocumentsList as $d)
                <div class="mb-2 flex items-start gap-2 rounded-xl border border-rose-200/70 bg-white p-3 last:mb-0">
                    <i data-lucide="file-clock" class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-rose-500" aria-hidden="true"></i>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-medium text-navy">{{ $d['name'] }} · {{ $d['type'] }}</p>
                        <p class="mt-0.5 text-[10px] text-muted-foreground">{{ $d['time'] }}</p>
                    </div>
                </div>
            @empty
                <p class="rounded-xl border border-dashed border-rose-200 bg-white p-4 text-xs text-muted-foreground">Tidak ada dokumen yang menunggu review.</p>
            @endforelse
        </section>

        <section class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm xl:col-span-7">
            <div class="flex items-center justify-between border-b border-border px-4 py-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500"><i data-lucide="users-round" class="h-4 w-4" aria-hidden="true"></i></span>
                    <div>
                        <h3 class="text-sm font-semibold text-navy">Pengajuan Terbaru</h3>
                        <p class="mt-0.5 text-xs text-muted-foreground">Pendaftar terbaru di portal peserta.</p>
                    </div>
                </div>
                <a href="{{ route('admin.pemeriksaan-dokumen') }}" class="text-xs font-semibold text-ocean">Buka Portal →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-emerald-50/60 text-muted-foreground">
                            <th class="px-4 py-3 text-left font-semibold">Nama Peserta</th>
                            <th class="px-4 py-3 text-left font-semibold">Layanan</th>
                            <th class="px-4 py-3 text-left font-semibold">Tahap</th>
                            <th class="px-4 py-3 text-left font-semibold">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentApplications as $row)
                            <tr class="border-t border-border transition-colors hover:bg-emerald-50/30">
                                <td class="max-w-[180px] truncate px-4 py-3 font-medium text-navy">{{ $row['name'] }}</td>
                                <td class="px-4 py-3"><x-admin.badge :color="$row['service'] === 'WOPPS' ? 'green' : 'blue'">{{ $row['service'] }}</x-admin.badge></td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $row['stage'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ $row['time'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">Belum ada pengajuan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
    window.dashboardData = {
        trend: @json($trend),
        statusData: @json($statusData),
    };
</script>
@endsection