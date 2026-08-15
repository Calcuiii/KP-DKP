@extends('layouts.admin')

@section('title', 'Kuota Lokasi KP - DKP Assistant')

@section('content')
<div class="space-y-5">

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-4">
        <x-admin.metric-card icon="map-pin" label="Total Lokasi" :value="$metrics['total']" color="ocean" />
        <x-admin.metric-card icon="check-circle" label="Kuota Tersedia" :value="$metrics['available']" color="teal" />
        <x-admin.metric-card icon="alert-circle" label="Kuota Terbatas" :value="$metrics['limited']" color="amber" />
        <x-admin.metric-card icon="x-circle" label="Penuh / Tidak Menerima" :value="$metrics['full']" color="red" />
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
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Status Kuota Saat Ini</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Terakhir Diperbarui</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Ubah Status</th>
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
                            <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">
                                {{ $location->quota_updated_at?->format('Y-m-d H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.internship-locations.update', $location) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="quota_status" class="rounded-lg border border-border bg-input-background px-2 py-1.5 text-xs">
                                        <option value="available" @selected($location->quota_status === 'available')>Tersedia</option>
                                        <option value="limited" @selected($location->quota_status === 'limited')>Terbatas</option>
                                        <option value="full" @selected($location->quota_status === 'full')>Penuh</option>
                                        <option value="unavailable" @selected($location->quota_status === 'unavailable')>Tidak Menerima</option>
                                        <option value="unknown" @selected($location->quota_status === 'unknown')>Belum Diperbarui</option>
                                    </select>
                                    <button type="submit" class="rounded-lg bg-ocean px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90">
                                        Simpan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection