@extends('layouts.admin')

@section('title', 'Pertanyaan Tidak Terjawab - DKP Assistant')

@section('content')
<div class="space-y-5">

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-4">
        <x-admin.metric-card icon="inbox" label="Total Tidak Terjawab" :value="$metrics['total']" color="red" />
        <x-admin.metric-card icon="alert-circle" label="Belum Ditinjau" :value="$metrics['baru']" color="amber" />
        <x-admin.metric-card icon="clock" label="Sedang Ditinjau" :value="$metrics['ditinjau']" color="ocean" />
        <x-admin.metric-card icon="check-circle" label="Selesai" :value="$metrics['selesai']" color="teal" />
    </div>

    <div class="flex gap-5">
        <div class="flex-1 overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
            <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-4">
                <h3 class="flex-1 text-sm font-semibold text-navy">Daftar Pertanyaan Tidak Terjawab</h3>
                <input
                    type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pertanyaan..."
                    class="rounded-xl border border-border bg-input-background px-3 py-2 text-xs focus:outline-none"
                >
                <button type="submit" class="rounded-xl border border-border px-3 py-2 text-xs font-semibold hover:bg-accent">
                    Cari
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-[#F4F7FB] text-muted-foreground">
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Pertanyaan</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Frekuensi</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Kategori</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Score</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Pertama</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Terakhir</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Status</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($questions as $q)
                            <tr class="cursor-pointer border-t border-border transition-colors hover:bg-[#F8FAFC] {{ $selected?->id === $q->id ? 'bg-[#F4F7FB]' : '' }}">
                                <td class="max-w-[200px] truncate px-4 py-3">{{ $q->question }}</td>
                                <td class="px-4 py-3 font-semibold text-ocean">{{ $q->frequency }}x</td>
                                <td class="px-4 py-3"><x-admin.badge>{{ $q->category }}</x-admin.badge></td>
                                <td class="px-4 py-3 text-muted-foreground">{{ number_format($q->score, 2) }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $q->first_asked->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $q->last_asked->format('Y-m-d') }}</td>
                                <td class="px-4 py-3"><x-admin.status-badge :status="$q->status" /></td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.unanswered-questions', ['selected' => $q->id, 'search' => request('search')]) }}" class="inline-block rounded-lg p-1.5 hover:bg-accent" title="Lihat Detail">
                                        <i data-lucide="eye" class="h-3 w-3" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-muted-foreground">
                                    Tidak ada pertanyaan tidak terjawab.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($selected)
            <div class="w-72 flex-shrink-0 space-y-4 rounded-2xl border border-border bg-card p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-navy">Detail Pertanyaan</h4>
                    <a href="{{ route('admin.unanswered-questions', ['search' => request('search')]) }}" class="rounded-lg p-1 hover:bg-accent">
                        <i data-lucide="x" class="h-3.5 w-3.5" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="rounded-xl border border-border bg-[#F4F7FB] p-3">
                    <p class="text-xs font-medium text-navy">{{ $selected->question }}</p>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Frekuensi</span>
                        <span class="font-semibold">{{ $selected->frequency }}x ditanyakan</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Kategori</span>
                        <x-admin.badge>{{ $selected->category }}</x-admin.badge>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Status</span>
                        <x-admin.status-badge :status="$selected->status" />
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Score tertinggi</span>
                        <span class="font-semibold">{{ number_format($selected->score, 2) }}</span>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-xs font-semibold text-navy">Respons Fallback Chatbot</p>
                    <p class="rounded-lg border border-border bg-[#F4F7FB] p-2.5 text-xs italic leading-relaxed text-muted-foreground">
                        "{{ $selected->fallback_response }}"
                    </p>
                </div>

                <div class="space-y-2">
                    <a href="{{ route('admin.knowledge-base') }}" class="block w-full rounded-xl bg-ocean px-3 py-2.5 text-center text-xs font-semibold text-white hover:opacity-90">
                        + Tambahkan Knowledge Base
                    </a>
                    <button type="button" class="w-full rounded-xl border border-border px-3 py-2.5 text-xs font-semibold hover:bg-accent">
                        Hubungkan ke Dokumen
                    </button>
                    <form method="POST" action="{{ route('admin.unanswered-questions.resolve', $selected) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl border border-emerald-300 px-3 py-2.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                            Tandai Selesai
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection