@extends('layouts.admin')

@section('title', 'Pertanyaan Tidak Terjawab - DKP Assistant')

@section('content')
<div class="space-y-5">

    <div class="grid gap-4 sm:grid-cols-2">
        <x-admin.metric-card icon="inbox" label="Pertanyaan Unik Tidak Terjawab" :value="$metrics['total']" color="red" />
        <x-admin.metric-card icon="alert-circle" label="Total Kejadian" :value="$metrics['total_occurrences']" color="amber" />
    </div>

    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
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
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Pertama Ditanyakan</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Terakhir Ditanyakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr class="border-t border-border transition-colors hover:bg-[#F8FAFC]">
                            <td class="max-w-[300px] truncate px-4 py-3">{{ $item['question'] }}</td>
                            <td class="px-4 py-3 font-semibold text-ocean">{{ $item['frequency'] }}x</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $item['first_asked']->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $item['last_asked']->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">
                                Tidak ada pertanyaan tidak terjawab.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection