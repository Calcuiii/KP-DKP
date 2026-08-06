@extends('layouts.admin')

@section('title', 'Analytics - Si-Molek')

@section('content')
@php
    $categoryMax = max($categoryData->pluck('value')->all() ?: [1]);
@endphp

<div class="space-y-6">

    <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-[#0B2A4D] via-[#124D84] to-ocean p-6 text-white shadow-lg sm:p-7">
        <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold tracking-[0.16em] text-sky-200">ANALISIS LAYANAN</p>
                <h1 class="mt-2 text-2xl font-bold sm:text-3xl">Pantau performa Si-Molek</h1>
                <p class="mt-2 text-sm leading-6 text-sky-100">Lihat tren pertanyaan, tingkat jawaban, dan sumber knowledge base yang paling banyak digunakan dalam satu tampilan.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:min-w-[330px]">
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                    <p class="text-xs text-sky-100">Periode analisis</p>
                    <p class="mt-1 text-lg font-bold">{{ $days }} hari</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                    <p class="text-xs text-sky-100">Tingkat jawaban</p>
                    <p class="mt-1 text-lg font-bold">{{ $answerRate }}%</p>
                </div>
            </div>
        </div>
    </section>

    <form method="GET" class="flex flex-wrap items-center gap-3 rounded-2xl border border-border bg-card p-3 shadow-sm">
        <div class="flex items-center gap-2 px-1 text-sm font-semibold text-navy">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-ocean/10 text-ocean">
                <i data-lucide="sliders-horizontal" class="h-4 w-4" aria-hidden="true"></i>
            </span>
            Filter laporan
        </div>
        <select name="period" class="rounded-xl border border-border bg-background px-3 py-2.5 text-xs font-medium shadow-sm" onchange="this.form.submit()">
            <option value="30" @selected($days == 30)>30 Hari Terakhir</option>
            <option value="7" @selected($days == 7)>7 Hari Terakhir</option>
            <option value="90" @selected($days == 90)>90 Hari Terakhir</option>
        </select>

        <a href="{{ route('admin.analytics.export', ['period' => $days]) }}" class="ml-auto flex items-center gap-1.5 rounded-xl bg-ocean px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#14518D]">
            <i data-lucide="download" class="h-3.5 w-3.5" aria-hidden="true"></i>
            Export Laporan
        </a>
    </form>

    <div class="grid gap-4 md:grid-cols-3">
        <x-admin.metric-card icon="message-square" label="Total Percakapan" :value="number_format($totalConversations)" color="ocean" />
        <x-admin.metric-card icon="hash" label="Total Pertanyaan" :value="number_format($totalQuestions)" color="teal" />
        <x-admin.metric-card icon="badge-check" label="Tingkat Jawaban" :value="$answerRate . '%'" sub="Jawaban berhasil diberikan" color="indigo" />
    </div>

    <div class="grid gap-5 xl:grid-cols-12">
        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm xl:col-span-7">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-navy">Aktivitas pertanyaan</p>
                    <p class="mt-1 text-xs text-muted-foreground">Perkembangan jumlah pertanyaan selama periode terpilih.</p>
                </div>
                <span class="rounded-full bg-ocean/10 px-3 py-1 text-xs font-semibold text-ocean">{{ number_format($totalQuestions) }} pertanyaan</span>
            </div>
            <div class="h-[260px]">
                <canvas id="questionTrendChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm xl:col-span-5">
            <div class="mb-5">
                <p class="text-sm font-semibold text-navy">Kategori paling banyak dicari</p>
                <p class="mt-1 text-xs text-muted-foreground">Topik yang paling sering muncul pada percakapan pengguna.</p>
            </div>

            <div class="h-[170px]">
                <canvas id="analyticsCategoryChart"></canvas>
            </div>

            <div class="mt-5 space-y-3 border-t border-border pt-4">
                @forelse ($categoryData->take(3) as $category)
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-ocean/10 text-xs font-bold text-ocean">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="mb-1 flex justify-between gap-3 text-xs">
                                <span class="truncate font-medium capitalize text-navy">{{ $category['name'] }}</span>
                                <span class="font-semibold text-muted-foreground">{{ $category['value'] }}</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-[#E4ECF6]">
                                <div class="h-full rounded-full bg-ocean" style="width: {{ round(($category['value'] / $categoryMax) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-muted-foreground">Belum ada kategori pertanyaan pada periode ini.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm xl:col-span-8">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-navy">Penggunaan knowledge base</p>
                    <p class="mt-1 text-xs text-muted-foreground">Distribusi sumber aktif berdasarkan jumlah chunk yang tersedia.</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                    <i data-lucide="database" class="h-4 w-4" aria-hidden="true"></i>
                </span>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @forelse ($kbUsage as $doc)
                    <div class="rounded-xl border border-border bg-background p-3.5">
                        <div class="flex justify-between gap-3 text-xs">
                            <span class="min-w-0 truncate font-semibold text-navy">{{ $doc['name'] }}</span>
                            <span class="shrink-0 font-bold text-ocean">{{ $doc['percentage'] }}%</span>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#E4ECF6]">
                            <div class="h-full rounded-full bg-gradient-to-r from-ocean to-teal" style="width: {{ $doc['percentage'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-muted-foreground">Belum ada dokumen yang terpakai chatbot.</p>
                @endforelse
            </div>
        </div>

        <aside class="rounded-2xl border border-border bg-[#F4F8FD] p-5 shadow-sm xl:col-span-4">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal/10 text-teal">
                <i data-lucide="lightbulb" class="h-5 w-5" aria-hidden="true"></i>
            </span>
            <h3 class="mt-4 text-sm font-semibold text-navy">Catatan analitik</h3>
            <p class="mt-2 text-sm leading-6 text-muted-foreground">
                Gunakan laporan ini untuk melihat topik yang perlu diprioritaskan pada knowledge base dan pertanyaan yang belum dapat dijawab.
            </p>
            <a href="{{ route('admin.unanswered-questions') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-ocean hover:underline">
                Tinjau pertanyaan belum terjawab
                <i data-lucide="arrow-up-right" class="h-4 w-4" aria-hidden="true"></i>
            </a>
        </aside>
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
