<details class="rounded-2xl border border-border bg-white p-4 shadow-sm" @if($usage['warning']) open @endif>
    <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-2 text-sm font-bold text-navy">
        <span>Pemantauan chatbot</span>
        <span class="rounded-full bg-light px-3 py-1 text-xs font-semibold">{{ $usage['enabled'] ? 'Groq · akun dinas terkonfirmasi' : 'Mode knowledge base · AI eksternal nonaktif' }}</span>
    </summary>
    <p class="mt-3 text-xs leading-relaxed text-muted-foreground">Penggunaan AI tambahan hari ini (WIB). FAQ dan jawaban langsung dari dokumen tetap tersedia saat kuota AI habis.</p>
    <dl class="mt-4 grid grid-cols-2 gap-4 text-sm lg:grid-cols-4">
        <div><dt class="text-xs text-muted-foreground">Panggilan AI</dt><dd class="mt-1 font-bold text-navy">{{ number_format($usage['requests']) }} / {{ number_format($usage['request_limit']) }}</dd></div>
        <div><dt class="text-xs text-muted-foreground">Anggaran token terpakai/dicadangkan</dt><dd class="mt-1 font-bold text-navy">{{ number_format($usage['tokens']) }} / {{ number_format($usage['token_limit']) }}</dd></div>
        <div><dt class="text-xs text-muted-foreground">Token dilaporkan penyedia</dt><dd class="mt-1 font-bold text-navy">{{ number_format($usage['actual_tokens']) }}</dd></div>
        <div><dt class="text-xs text-muted-foreground">Panggilan gagal</dt><dd class="mt-1 font-bold text-navy">{{ number_format($usage['failures']) }}</dd></div>
        <div><dt class="text-xs text-muted-foreground">Jawaban tersimpan digunakan</dt><dd class="mt-1 font-bold text-navy">{{ number_format($usage['cache_hits']) }}</dd></div>
        <div><dt class="text-xs text-muted-foreground">Kembali ke dokumen</dt><dd class="mt-1 font-bold text-navy">{{ number_format($usage['fallbacks']) }}</dd></div>
    </dl>
    @if($usage['warning'])
        <p class="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-900">Anggaran AI mendekati atau mencapai batas. Chatbot akan menggunakan jawaban dokumen, tanpa berpindah ke layanan berbayar.</p>
    @endif
    @if($usage['last_error'])
        <p class="mt-3 text-xs text-muted-foreground">Status teknis terakhir: <code>{{ $usage['last_error'] }}</code></p>
    @endif
    @if(!empty($usage['provider_limits']))
        <div class="mt-4 border-t border-border pt-3 text-xs text-muted-foreground">
            <p>Snapshot Groq terakhir: {{ $usage['provider_limits']['observed_at'] ?? 'Belum tersedia' }}. Angka ini bukan saldo uang atau jaminan kuota saat ini.</p>
            <p class="mt-1">Sisa permintaan harian: {{ $usage['provider_limits']['x-ratelimit-remaining-requests'] ?? '—' }} · Sisa token per menit: {{ $usage['provider_limits']['x-ratelimit-remaining-tokens'] ?? '—' }}</p>
        </div>
    @endif
    @if(!$usage['enabled'])
        <p class="mt-4 text-xs leading-relaxed text-muted-foreground">Aktifkan AI tambahan hanya setelah pengelola mengonfirmasi akun dinas pada Groq Free Plan. Status paket harus diperiksa pada dashboard Groq; aplikasi tidak dapat membuktikan billing dari API key.</p>
    @endif
</details>
