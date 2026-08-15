@extends('layouts.admin')

@section('title', 'Pertanyaan Tidak Terjawab - Si-Molek')

@section('content')
<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-xl border border-teal/25 bg-teal/10 px-4 py-3 text-sm font-semibold text-teal">{{ session('status') }}</div>
    @endif

    <div>
        <h2 class="text-xl font-bold text-navy">Tindak lanjut pertanyaan</h2>
        <p class="mt-1 text-sm text-muted-foreground">Pertanyaan di bawah telah dipilih pengguna untuk diteruskan kepada petugas.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.metric-card icon="inbox" label="Perlu Ditindaklanjuti" :value="$metrics['open']" color="red" />
        <x-admin.metric-card icon="check-circle" label="Sudah Selesai" :value="$metrics['resolved']" color="teal" />
        <x-admin.metric-card icon="message-square" label="Semua Fallback Chatbot" :value="$metrics['raw_unanswered']" color="ocean" />
        <x-admin.metric-card icon="alert-circle" label="WhatsApp Gagal" :value="$metrics['whatsapp_failed']" color="amber" />
    </div>

    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-4">
            <h3 class="min-w-48 flex-1 text-sm font-semibold text-navy">Tiket yang diteruskan</h3>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau pertanyaan..." class="rounded-xl border border-border bg-input-background px-3 py-2 text-xs focus:outline-none">
            <button type="submit" class="rounded-xl border border-border px-3 py-2 text-xs font-semibold hover:bg-accent">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-[#F4F7FB] text-muted-foreground">
                        <th class="px-4 py-3 text-left font-semibold">Kode</th>
                        <th class="px-4 py-3 text-left font-semibold">Pertanyaan</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">WhatsApp</th>
                        <th class="px-4 py-3 text-left font-semibold">Diteruskan</th>
                        <th class="px-4 py-3 text-right font-semibold">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $statusClass = $item->status === 'resolved' ? 'bg-teal/10 text-teal' : 'bg-red-50 text-red-700';
                            $waClass = match ($item->whatsapp_status) {
                                'sent' => 'bg-teal/10 text-teal',
                                'failed' => 'bg-red-50 text-red-700',
                                default => 'bg-amber-50 text-amber-700',
                            };
                        @endphp
                        <tr class="border-t border-border align-top transition-colors hover:bg-[#F8FAFC]">
                            <td class="whitespace-nowrap px-4 py-4 font-bold text-ocean"><a href="{{ route('admin.unanswered-questions.show', $item) }}" class="hover:underline">{{ $item->ticket_code }}</a></td>
                            <td class="max-w-lg px-4 py-4">
                                <p class="font-medium leading-5 text-navy">{{ $item->userMessage->content }}</p>
                                <p class="mt-1 text-[10px] text-muted-foreground">Percakapan #{{ $item->assistantMessage->chat_conversation_id }}</p>
                            </td>
                            <td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 font-semibold {{ $statusClass }}">{{ $item->status === 'resolved' ? 'Selesai' : 'Baru' }}</span></td>
                            <td class="px-4 py-4">
                                <span class="rounded-full px-2.5 py-1 font-semibold {{ $waClass }}">{{ ucfirst($item->whatsapp_status) }}</span>
                                @if ($item->whatsapp_error)<p class="mt-2 max-w-52 text-[10px] leading-4 text-red-600" title="{{ $item->whatsapp_error }}">Pengiriman perlu diperiksa.</p>@endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-muted-foreground">{{ $item->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</td>
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('admin.unanswered-questions.show', $item) }}" class="inline-flex whitespace-nowrap rounded-xl bg-navy px-3 py-2 font-semibold text-white hover:bg-ocean">{{ $item->status === 'resolved' ? 'Lihat jawaban' : 'Tinjau & jawab' }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-muted-foreground">Belum ada pertanyaan yang diteruskan pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="border-t border-border px-5 py-4">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection
