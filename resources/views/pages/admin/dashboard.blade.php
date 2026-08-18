@extends('layouts.admin')

@section('title', 'Dashboard - SI-MELAYUR')

@section('content')
<div class="space-y-5">
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-navy via-ocean to-[#2875be] px-5 py-6 text-white shadow-sm sm:px-7">
        <div class="relative z-10 max-w-2xl">
            <p class="text-xs font-medium tracking-wide text-cyan-100">RINGKASAN LAYANAN</p>
            <h2 class="mt-1 text-xl font-bold sm:text-2xl">Selamat datang, {{ auth()->user()->name }}</h2>
            <p class="mt-2 text-sm leading-relaxed text-blue-100">Pantau aktivitas chatbot, pertanyaan pengguna, dan kesiapan knowledge base dari satu tempat.</p>
        </div>
        <div class="relative z-10 mt-4 flex flex-wrap gap-2">
            <a href="{{ route('admin.conversation-logs') }}" class="rounded-xl bg-white/15 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/25">Lihat Log Percakapan</a>
            <a href="{{ route('admin.unanswered-questions') }}" class="rounded-xl border border-white/35 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/10">Tinjau Pertanyaan</a>
        </div>
        <div class="absolute -right-12 -top-16 h-48 w-48 rounded-full border-[28px] border-white/10"></div>
        <div class="absolute right-24 top-8 h-5 w-5 rounded-full bg-cyan-200/30"></div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($metrics as $m)
            <x-admin.metric-card
                :icon="$m['icon']" :label="$m['label']"
                :value="$m['value']" :sub="$m['sub']" :color="$m['color']"
            />
        @endforeach
    </div>

    <div class="grid gap-5 xl:grid-cols-12">
        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm xl:col-span-8">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-navy">Tren Pertanyaan Chatbot</p>
                    <p class="mt-0.5 text-xs text-muted-foreground">Aktivitas pengguna selama 30 hari terakhir.</p>
                </div>
                <a href="{{ route('admin.analytics') }}" class="text-xs font-semibold text-ocean">Analytics →</a>
            </div>
            <canvas id="trendChart" height="90"></canvas>
        </div>

        <aside class="space-y-5 xl:col-span-4">
            <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-navy">Status Jawaban</h3>
                <canvas id="statusChart" class="mx-auto mt-3" height="130"></canvas>
                <div class="mt-3 space-y-2">
                    @foreach ($statusData as $s)
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full" style="background:{{ $s['color'] }}"></span>
                                {{ $s['name'] }}
                            </div>
                            <span class="font-semibold text-navy">{{ $s['value'] }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-navy">Akses Cepat</h3>
                <div class="mt-3 space-y-2">
                    <a href="{{ route('admin.conversation-logs') }}" class="flex items-center justify-between rounded-xl bg-[#F4F7FB] px-3 py-2.5 text-xs font-medium text-navy transition hover:bg-secondary">
                        <span class="flex items-center gap-2"><i data-lucide="message-square" class="h-3.5 w-3.5 text-ocean" aria-hidden="true"></i>Log Percakapan</span>
                        <i data-lucide="chevron-right" class="h-3.5 w-3.5 text-muted-foreground" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('admin.unanswered-questions') }}" class="flex items-center justify-between rounded-xl bg-[#F4F7FB] px-3 py-2.5 text-xs font-medium text-navy transition hover:bg-secondary">
                        <span class="flex items-center gap-2"><i data-lucide="inbox" class="h-3.5 w-3.5 text-amber-500" aria-hidden="true"></i>Pertanyaan Tidak Terjawab</span>
                        <i data-lucide="chevron-right" class="h-3.5 w-3.5 text-muted-foreground" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('admin.analytics') }}" class="flex items-center justify-between rounded-xl bg-[#F4F7FB] px-3 py-2.5 text-xs font-medium text-navy transition hover:bg-secondary">
                        <span class="flex items-center gap-2"><i data-lucide="trending-up" class="h-3.5 w-3.5 text-teal" aria-hidden="true"></i>Analytics</span>
                        <i data-lucide="chevron-right" class="h-3.5 w-3.5 text-muted-foreground" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </aside>
    </div>

    <div class="grid gap-5 xl:grid-cols-12">
        <section class="rounded-2xl border border-border bg-card p-5 shadow-sm xl:col-span-5">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-navy">Perlu Ditinjau</h3>
                    <p class="mt-0.5 text-xs text-muted-foreground">Pertanyaan terbaru yang belum terjawab.</p>
                </div>
                <a href="{{ route('admin.unanswered-questions') }}" class="text-xs font-semibold text-ocean">Lihat Semua →</a>
            </div>
            @forelse ($unansweredList as $u)
                <div class="mb-2 flex items-start gap-2 rounded-xl border border-border bg-[#F4F7FB] p-3 last:mb-0">
                    <i data-lucide="alert-circle" class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-amber-500" aria-hidden="true"></i>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-medium text-navy">{{ $u['question'] }}</p>
                        <p class="mt-0.5 text-[10px] text-muted-foreground">{{ $u['time'] }}</p>
                    </div>
                </div>
            @empty
                <p class="rounded-xl border border-dashed border-border p-4 text-xs text-muted-foreground">Belum ada pertanyaan yang perlu ditinjau.</p>
            @endforelse
        </section>

        <section class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm xl:col-span-7">
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div>
                    <h3 class="text-sm font-semibold text-navy">Pertanyaan Terbaru</h3>
                    <p class="mt-0.5 text-xs text-muted-foreground">Aktivitas percakapan pengguna terbaru.</p>
                </div>
                <a href="{{ route('admin.conversation-logs') }}" class="text-xs font-semibold text-ocean">Buka Log →</a>
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
                        @forelse ($recentQuestions as $row)
                            <tr class="border-t border-border transition-colors hover:bg-[#F8FAFC]">
                                <td class="max-w-[220px] truncate px-4 py-3">{{ $row['question'] }}</td>
                                <td class="px-4 py-3"><x-admin.badge>{{ $row['category'] }}</x-admin.badge></td>
                                <td class="px-4 py-3"><x-admin.status-badge :status="$row['status']" /></td>
                                <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ $row['time'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">Belum ada percakapan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

@push('scripts')
<script>
    window.dashboardData = {
        trend: @json($trend),
        statusData: @json($statusData),
    };
</script>
@endpush
@endsection
