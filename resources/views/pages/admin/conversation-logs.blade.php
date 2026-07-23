@extends('layouts.admin')

@section('title', 'Conversation Logs - DKP Assistant')

@section('content')
<div class="space-y-5">

    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-4">
            <h3 class="flex-1 text-sm font-semibold text-navy">Log Percakapan</h3>

            <input
                type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari pertanyaan..."
                class="rounded-xl border border-border bg-input-background px-3 py-2 text-xs focus:outline-none"
            >

            <select name="status" class="rounded-xl border border-border bg-input-background px-3 py-2 text-xs" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                @foreach (['Dijawab', 'Tidak Ditemukan', 'Error'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>

            <select name="category" class="rounded-xl border border-border bg-input-background px-3 py-2 text-xs" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach (['Persyaratan', 'Alur', 'Dokumen', 'Umum'] as $c)
                    <option value="{{ $c }}" @selected(request('category') === $c)>{{ $c }}</option>
                @endforeach
            </select>

            <button type="submit" class="rounded-xl border border-border px-3 py-2 text-xs font-semibold hover:bg-accent">
                Cari
            </button>

            <a href="{{ route('admin.conversation-logs.export', request()->query()) }}" class="flex items-center gap-1.5 rounded-xl border border-border px-3 py-2 text-xs font-semibold hover:bg-accent">
                <i data-lucide="download" class="h-3.5 w-3.5" aria-hidden="true"></i>
                Export
            </a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-[#F4F7FB] text-muted-foreground">
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">ID</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Pertanyaan</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Kategori</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Sumber</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Score</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Waktu</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Tanggal</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-t border-border transition-colors hover:bg-[#F8FAFC]">
                            <td class="px-4 py-3 font-mono text-muted-foreground">{{ $log->code }}</td>
                            <td class="max-w-[180px] truncate px-4 py-3">{{ $log->question }}</td>
                            <td class="px-4 py-3"><x-admin.badge>{{ $log->category }}</x-admin.badge></td>
                            <td class="px-4 py-3"><x-admin.status-badge :status="$log->status" /></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $log->sources }} dok</td>
                            <td class="px-4 py-3 font-semibold {{ $log->score > 0.8 ? 'text-teal' : 'text-amber-500' }}">
                                {{ number_format($log->score, 2) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $log->response_time }}s</td>
                            <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                <button type="button" class="rounded-lg p-1.5 hover:bg-accent" title="Lihat Detail">
                                    <i data-lucide="eye" class="h-3 w-3" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-10 text-center text-muted-foreground">
                                Belum ada log percakapan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-border px-5 py-3 text-xs text-muted-foreground">
            <span>Menampilkan {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} dari {{ number_format($total) }} percakapan</span>
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection