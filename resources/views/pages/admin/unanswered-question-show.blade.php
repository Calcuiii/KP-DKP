@extends('layouts.admin')

@section('title', 'Detail Tiket - SI-MELAYUR')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('admin.unanswered-questions') }}" class="text-xs font-semibold text-ocean hover:underline">← Kembali ke daftar tiket</a>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-navy">{{ $escalation->ticket_code }}</h2>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $escalation->status === 'resolved' ? 'bg-teal/10 text-teal' : 'bg-red-50 text-red-700' }}">
                    {{ $escalation->status === 'resolved' ? 'Selesai' : 'Perlu ditindaklanjuti' }}
                </span>
            </div>
            <p class="mt-1 text-sm text-muted-foreground">Diteruskan {{ $escalation->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
        </div>
        <div class="rounded-xl bg-[#F4F7FB] px-4 py-3 text-xs">
            <span class="text-muted-foreground">Status WhatsApp</span>
            <span class="ml-2 font-bold text-navy">{{ ucfirst($escalation->whatsapp_status) }}</span>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-teal/25 bg-teal/10 px-4 py-3 text-sm font-semibold text-teal">{{ session('status') }}</div>
    @endif

    <div class="grid gap-5 lg:grid-cols-[1fr_.8fr]">
        <section class="rounded-2xl border border-border bg-white shadow-sm">
            <div class="border-b border-border px-5 py-4">
                <h3 class="text-sm font-bold text-navy">Konteks percakapan</h3>
                <p class="mt-1 text-xs text-muted-foreground">Gunakan percakapan sebelumnya agar jawaban petugas sesuai konteks pengguna.</p>
            </div>
            <div class="max-h-[620px] space-y-4 overflow-y-auto p-5">
                @foreach ($escalation->assistantMessage->conversation->messages as $message)
                    <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[88%] rounded-2xl px-4 py-3 text-sm leading-6 {{ $message->role === 'user' ? 'rounded-tr-sm bg-ocean text-white' : ($message->status === 'admin_answer' ? 'rounded-tl-sm border border-teal/30 bg-teal/10 text-navy' : 'rounded-tl-sm border border-border bg-[#F8FAFC] text-navy') }}">
                            @if ($message->status === 'admin_answer')
                                <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-teal">Jawaban petugas</p>
                            @endif
                            <p class="whitespace-pre-wrap">{{ $message->content }}</p>
                            <p class="mt-1 text-[10px] {{ $message->role === 'user' ? 'text-blue-100' : 'text-muted-foreground' }}">{{ $message->created_at->timezone('Asia/Jakarta')->format('d M H:i') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="h-fit rounded-2xl border border-border bg-white p-5 shadow-sm">
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-xs font-bold text-amber-800">Pertanyaan yang diteruskan</p>
                <p class="mt-2 text-sm font-semibold leading-6 text-navy">{{ $escalation->userMessage->content }}</p>
            </div>

            <form method="POST" action="{{ route('admin.unanswered-questions.respond', $escalation) }}" class="mt-5">
                @csrf
                <label for="response" class="text-sm font-bold text-navy">Jawaban petugas</label>
                <p class="mt-1 text-xs leading-5 text-muted-foreground">Jawaban akan muncul pada riwayat percakapan pengguna saat percakapan dibuka kembali.</p>
                <textarea id="response" name="response" rows="10" maxlength="5000" class="mt-3 w-full resize-y rounded-xl border border-border bg-input-background px-4 py-3 text-sm leading-6 focus:border-ocean focus:outline-none" placeholder="Tuliskan jawaban resmi dan jelas...">{{ old('response', $escalation->admin_response) }}</textarea>
                @error('response')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror

                <div class="mt-4 rounded-xl bg-[#F4F7FB] p-3 text-xs leading-5 text-muted-foreground">
                    Pastikan jawaban tidak memuat data pribadi dan hanya menggunakan informasi yang telah dikonfirmasi oleh Dinas.
                </div>

                <button type="submit" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-navy px-4 py-3 text-sm font-bold text-white transition hover:bg-ocean">
                    <i data-lucide="send" class="h-4 w-4" aria-hidden="true"></i>
                    {{ $escalation->response_message_id ? 'Perbarui jawaban' : 'Kirim jawaban dan selesaikan' }}
                </button>
            </form>
        </section>
    </div>
</div>
@endsection
