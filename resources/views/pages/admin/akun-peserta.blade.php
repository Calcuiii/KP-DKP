@extends('layouts.admin')

@section('title', 'Akun Peserta - SI-MELAYUR')

@section('content')
<div class="space-y-5">

    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        <div class="flex flex-col gap-3 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-sm font-semibold text-navy">Daftar Akun Peserta</h3>

            <form method="GET" class="flex items-center gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama, email, universitas..."
                    class="w-64 rounded-xl border border-border bg-input-background px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                <button type="submit" class="rounded-xl bg-ocean px-3 py-2 text-xs font-semibold text-white">
                    Cari
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-[#F4F7FB] text-muted-foreground">
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Nama Lengkap</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Email</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Universitas</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">No. WhatsApp</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Status Email</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($participants as $participant)
                        <tr class="border-t border-border transition-colors hover:bg-[#F8FAFC]">
                            <td class="px-4 py-3 font-medium">{{ $participant->name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $participant->email }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $participant->institution }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $participant->phone }}</td>
                            <td class="px-4 py-3">
                                @if ($participant->email_verified_at)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">Terverifikasi</span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700">Belum Verifikasi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $participant->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-muted-foreground">
                                Belum ada peserta yang mendaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($participants->hasPages())
            <div class="border-t border-border p-5">
                {{ $participants->links() }}
            </div>
        @endif
    </div>
</div>
@endsection