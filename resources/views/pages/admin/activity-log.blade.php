@extends('layouts.admin')

@section('title', 'Activity Log - DKP Assistant')

@section('content')
<div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
    <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-4">
        <h3 class="flex-1 text-sm font-semibold text-navy">Log Aktivitas Administrator</h3>

        <input
            type="text" name="search" value="{{ request('search') }}"
            placeholder="Cari aktivitas..."
            class="rounded-xl border border-border bg-input-background px-3 py-2 text-xs focus:outline-none"
        >

        <select name="admin" class="rounded-xl border border-border bg-input-background px-3 py-2 text-xs" onchange="this.form.submit()">
            <option value="">Semua Admin</option>
            @foreach ($admins as $admin)
                <option value="{{ $admin->id }}" @selected(request('admin') == $admin->id)>{{ $admin->name }}</option>
            @endforeach
        </select>

        <select name="module" class="rounded-xl border border-border bg-input-background px-3 py-2 text-xs" onchange="this.form.submit()">
            <option value="">Semua Modul</option>
            @foreach ($modules as $module)
                <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
            @endforeach
        </select>

        <button type="submit" class="rounded-xl border border-border px-3 py-2 text-xs font-semibold hover:bg-accent">
            Cari
        </button>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-[#F4F7FB] text-muted-foreground">
                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Administrator</th>
                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Aksi</th>
                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Modul</th>
                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Deskripsi</th>
                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Tanggal & Waktu</th>
                    <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t border-border transition-colors hover:bg-[#F8FAFC]">
                        <td class="px-4 py-3 font-medium">{{ $log->user?->name ?? 'Pengguna Dihapus' }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :status="$log->action" /></td>
                        <td class="px-4 py-3 text-ocean">{{ $log->module }}</td>
                        <td class="max-w-[280px] truncate px-4 py-3 text-muted-foreground">{{ $log->description }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-muted-foreground">
                            Belum ada aktivitas tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($logs->hasPages())
        <div class="border-t border-border px-5 py-3">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection