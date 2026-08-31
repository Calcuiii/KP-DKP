@extends('layouts.admin')

@section('title', 'Kuota Lokasi KP - DKP Assistant')

@section('content')
<div class="space-y-5">

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-5">
        <x-admin.metric-card icon="map-pin" label="Total Lokasi" :value="$metrics['total']" color="ocean" />
        <x-admin.metric-card icon="check-circle" label="Lokasi Berkuota Tersedia" :value="$metrics['available']" color="teal" />
        <x-admin.metric-card icon="alert-circle" label="Kuota Terbatas" :value="$metrics['limited']" color="amber" />
        <x-admin.metric-card icon="x-circle" label="Penuh / Tidak Menerima" :value="$metrics['full']" color="red" />
        <x-admin.metric-card icon="users" label="Total Jumlah Kuota" :value="$metrics['total_quota'] ?? 0" color="ocean" />
    </div>

    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        <div class="border-b border-border px-5 py-4">
            <h3 class="text-sm font-semibold text-navy">Daftar Lokasi Kerja Praktik / Magang</h3>
            <p class="mt-0.5 text-xs text-muted-foreground">Perubahan di sini langsung terlihat oleh peserta di halaman dashboard mereka.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-[#F4F7FB] text-muted-foreground">
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">No</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Nama Lokasi</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Status Kuota</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Jumlah Kuota</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Terakhir Diperbarui</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($locations as $location)
                        <tr class="border-t border-border transition-colors hover:bg-[#F8FAFC]">
                            <td class="px-4 py-3 text-muted-foreground">{{ $location->display_order }}</td>
                            <td class="max-w-[280px] px-4 py-3 font-medium text-navy">{{ $location->name }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $badgeClass = match ($location->quota_status) {
                                        'available' => 'bg-emerald-100 text-emerald-700',
                                        'limited' => 'bg-amber-100 text-amber-700',
                                        'full', 'unavailable' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-medium {{ $badgeClass }}">
                                    {{ $location->quotaLabel() }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-navy">
                                {{ $location->quota_available !== null ? $location->quota_available . ' orang' : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">
                                {{ $location->quota_updated_at?->format('Y-m-d H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    type="button"
                                    data-open-modal="edit-quota-modal-{{ $location->id }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-ocean px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90"
                                >
                                    <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                    Edit Kuota
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Ubah Kuota per Lokasi --}}
@foreach ($locations as $location)
    <div data-modal="edit-quota-modal-{{ $location->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-navy">Ubah Kondisi Kuota</h3>
                    <p class="mt-1 text-xs text-muted-foreground">{{ $location->name }}</p>
                </div>
                <button type="button" data-close-modal="edit-quota-modal-{{ $location->id }}" class="rounded-xl p-2 hover:bg-accent">
                    <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.internship-locations.update', $location) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-1.5 block text-xs font-semibold">Status Kuota</label>
                    <select name="quota_status" class="w-full rounded-xl border border-border bg-input-background px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="available" @selected($location->quota_status === 'available')>Tersedia</option>
                        <option value="limited" @selected($location->quota_status === 'limited')>Terbatas</option>
                        <option value="full" @selected($location->quota_status === 'full')>Penuh</option>
                        <option value="unavailable" @selected($location->quota_status === 'unavailable')>Tidak Menerima</option>
                        <option value="unknown" @selected($location->quota_status === 'unknown')>Belum Diperbarui</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold">Jumlah Kuota</label>
                    <input
                        type="number"
                        name="quota_available"
                        min="0"
                        placeholder="Contoh: 5"
                        value="{{ $location->quota_available }}"
                        class="w-full rounded-xl border border-border bg-input-background px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                    <p class="mt-1.5 text-[11px] text-muted-foreground">Kosongkan jika jumlah pasti belum ditentukan.</p>
                </div>

                <button type="submit" class="w-full rounded-xl bg-ocean py-3 text-sm font-semibold text-white hover:opacity-90">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
@endforeach
@endsection