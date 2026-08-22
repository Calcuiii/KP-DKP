@php
    use App\Models\ParticipantApplicationDocument;

    $guestbookProof = $application->latestDocument(ParticipantApplicationDocument::TYPE_GUESTBOOK);
    $requestLetter = $application->latestDocument(ParticipantApplicationDocument::TYPE_REQUEST_LETTER);
    $letterApproved = $application->requestLetterApproved();
    $letterNeedsRevision = $requestLetter?->review_status === ParticipantApplicationDocument::REVIEW_REVISION;
    $automatedNeedsRevision = in_array($requestLetter?->automated_check_status, ['needs_revision', 'unreadable'], true);
    $canUploadLetter = ! $requestLetter || $letterNeedsRevision || $automatedNeedsRevision;
    $officialStarted = $application->official_started_at;
    $officialEnded = $application->official_ended_at;
    $today = now()->startOfDay();
    $daysRemaining = $officialEnded ? max(0, $today->diffInDays($officialEnded->copy()->startOfDay(), false)) : null;
    $availableCount = $locations->where('quota_status', 'available')->count();
    $stageFiveUnlocked = $application->google_form_confirmed_at !== null;
    $stageSixUnlocked = $officialStarted !== null || in_array(mb_strtolower((string) $application->decision), ['accepted', 'approved', 'diterima'], true);
@endphp

<section id="persiapan">
    <article class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
        <div class="flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $guestbookProof ? 'bg-teal text-white' : 'bg-ocean/10 text-ocean' }}"><i data-lucide="book-open" class="h-5 w-5"></i></span>
            <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-ocean">Tahap 1</p><h2 class="mt-1 text-xl font-extrabold">Isi dan buktikan Buku Tamu</h2><p class="mt-2 text-sm leading-relaxed text-muted-foreground">Pengisian dilakukan secara individu. Setelah selesai, unggah screenshot atau PDF sebagai bukti.</p></div>
        </div>

        @if ($guestbookProof)
            <div class="mt-6 flex flex-col gap-4 rounded-2xl border border-teal/25 bg-teal/[0.06] p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3"><i data-lucide="check-circle" class="h-5 w-5 text-teal"></i><div><p class="text-sm font-bold">Bukti sudah diunggah</p><p class="text-xs text-muted-foreground">{{ $guestbookProof->original_name }} · {{ $guestbookProof->created_at->format('d M Y, H:i') }}</p></div></div>
                <a href="{{ route('peserta.document.download', $guestbookProof) }}" class="text-xs font-bold text-ocean">Lihat bukti</a>
            </div>
        @else
            <div class="mt-6 rounded-2xl border border-ocean/20 bg-ocean/[0.05] p-4 text-sm leading-relaxed text-muted-foreground">
                Formulir akan terbuka di tab baru. Setelah selesai mengisi, kembali ke dashboard ini untuk mengunggah bukti pengisian.
            </div>
            <div class="mt-4 flex flex-wrap gap-3"><a href="{{ $guestbookUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white"><i data-lucide="external-link" class="h-4 w-4"></i> Buka Form Buku Tamu</a></div>
            <form method="POST" action="{{ route('peserta.guestbook-proof.store') }}" enctype="multipart/form-data" class="mt-5 rounded-2xl border border-dashed border-ocean/30 bg-background p-5">
                @csrf
                <label class="text-sm font-bold">Upload bukti pengisian</label><input type="file" name="guestbook_proof" accept=".jpg,.jpeg,.png,.pdf" required class="mt-3 block w-full rounded-xl border border-border bg-white p-3 text-sm">
                @error('guestbook_proof')<p class="mt-2 text-xs font-semibold text-destructive">{{ $message }}</p>@enderror
                <label class="mt-4 flex items-start gap-3 text-xs leading-relaxed text-muted-foreground"><input type="checkbox" name="guestbook_declaration" value="1" required class="mt-0.5">Saya menyatakan telah mengisi Buku Tamu dengan data yang benar.</label>
                <button class="mt-4 rounded-xl bg-ocean px-5 py-2.5 text-sm font-bold text-white">Simpan Bukti</button>
            </form>
        @endif
        <div class="mt-6 flex items-start gap-3 rounded-2xl border border-ocean/15 bg-ocean/[0.05] p-4">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-ocean/10 text-ocean"><i data-lucide="info" class="h-4 w-4"></i></span>
            <div><p class="text-sm font-extrabold">Mengapa Buku Tamu wajib?</p><p class="mt-1 text-xs leading-relaxed text-muted-foreground">Buku Tamu menjadi pendataan awal peserta sebelum surat diproses. Bukti dapat berupa screenshot halaman selesai atau PDF respons pengisian, maksimal 5 MB.</p></div>
        </div>
    </article>
</section>

<section id="lokasi-kuota" class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8 {{ $guestbookProof ? '' : 'opacity-60' }}">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Tahap 2 · Informasi</p><h2 class="mt-2 text-2xl font-extrabold">Lokasi dan ketersediaan kuota</h2><p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground">Gunakan informasi ini untuk menentukan lokasi tujuan di dalam surat. Status kuota bukan jaminan penerimaan.</p></div><span class="rounded-full bg-teal/10 px-4 py-2 text-xs font-bold text-teal">{{ $availableCount }} lokasi tersedia</span></div>
    <div class="mt-5 flex flex-wrap gap-2" aria-label="Keterangan status kuota">
        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-bold text-emerald-700"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Kuota tersedia</span>
        <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-[11px] font-bold text-amber-700"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Kuota terbatas</span>
        <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1.5 text-[11px] font-bold text-red-700"><span class="h-2 w-2 rounded-full bg-red-500"></span>Kuota penuh / tidak menerima</span>
        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-[11px] font-bold text-slate-600"><span class="h-2 w-2 rounded-full bg-slate-400"></span>Belum diperbarui</span>
    </div>
    @if (! $guestbookProof)<div class="mt-5 rounded-2xl bg-amber-50 p-4 text-sm font-semibold text-amber-800">Upload bukti Buku Tamu untuk membuka informasi lengkap lokasi.</div>@else
        <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($locations as $location)
                @php
                    $quotaClass = match($location->quota_status) { 'available' => 'bg-emerald-50 text-emerald-700', 'limited' => 'bg-amber-50 text-amber-700', 'full', 'unavailable' => 'bg-red-50 text-red-700', default => 'bg-slate-100 text-slate-600' };
                @endphp
                <div class="rounded-2xl border border-border bg-background p-4"><div class="flex gap-3"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-xs font-extrabold text-ocean shadow-sm">{{ $location->display_order }}</span><div><h3 class="text-sm font-bold leading-snug">{{ $location->name }}</h3><span class="mt-3 inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold {{ $quotaClass }}">{{ $location->quotaLabel() }}</span>@if($location->quota_updated_at)<p class="mt-2 text-[10px] text-muted-foreground">Diperbarui {{ $location->quota_updated_at->diffForHumans() }}</p>@endif</div></div></div>
            @endforeach
        </div>
    @endif
</section>

<section id="surat-permohonan">
    <article class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8 {{ $guestbookProof ? '' : 'pointer-events-none opacity-60' }}">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
            <div class="max-w-2xl"><p class="text-xs font-bold uppercase tracking-[0.18em] text-ocean">Tahap 3</p><h2 class="mt-2 text-2xl font-extrabold">Siapkan dan upload surat permohonan</h2><p class="mt-2 text-sm leading-relaxed text-muted-foreground">Pastikan surat ditujukan kepada Kepala Dinas serta mencantumkan lokasi, tanggal mulai dan selesai, dan identitas peserta. Upload dalam format PDF maksimal 10 MB.</p></div>
            @if($requestLetter)<span class="inline-flex w-fit rounded-full px-3 py-1.5 text-xs font-bold {{ ($letterNeedsRevision || $automatedNeedsRevision) ? 'bg-red-50 text-red-700' : ($letterApproved ? 'bg-teal/10 text-teal' : 'bg-amber-50 text-amber-700') }}">{{ ($letterNeedsRevision || $automatedNeedsRevision) ? 'Perlu perbaikan' : ($letterApproved ? 'Lolos' : 'Menunggu admin') }}</span>@endif
        </div>
        @if ($letterNeedsRevision)
            <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4"><p class="text-sm font-bold text-red-800">Surat perlu diperbaiki</p><p class="mt-1 text-sm leading-relaxed text-red-700">{{ $requestLetter->review_notes ?: 'Silakan periksa kembali kelengkapan surat dan unggah versi terbaru.' }}</p></div>
        @elseif ($requestLetter)
            <div class="mt-5 rounded-2xl border border-border bg-background p-4 text-sm"><p class="font-bold">Versi {{ $requestLetter->version }} · {{ $requestLetter->original_name }}</p><p class="mt-1 text-xs text-muted-foreground">Dikirim {{ $requestLetter->created_at->format('d M Y, H:i') }}</p><a href="{{ route('peserta.document.download', $requestLetter) }}" class="mt-3 inline-flex text-xs font-bold text-ocean">Unduh surat</a></div>
        @endif
        @if ($requestLetter?->automated_check_results)
            @php $automatedResult = $requestLetter->automated_check_results; @endphp
            <div class="mt-5 rounded-2xl border {{ $automatedNeedsRevision ? 'border-red-200 bg-red-50/60' : 'border-teal/25 bg-teal/[0.05]' }} p-5">
                <div class="flex items-start gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $automatedNeedsRevision ? 'bg-red-100 text-red-700' : 'bg-teal/10 text-teal' }}"><i data-lucide="scan-search" class="h-4 w-4"></i></span><div><p class="text-sm font-extrabold">Hasil pemeriksaan otomatis</p><p class="mt-1 text-xs leading-relaxed text-muted-foreground">{{ $automatedResult['summary'] }}</p></div></div>
                <div class="mt-4 space-y-2">
                    @foreach ($automatedResult['checks'] as $check)
                        <div class="flex items-start gap-3 rounded-xl bg-white/80 px-3 py-2.5"><span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold {{ $check['status'] === 'passed' ? 'bg-emerald-100 text-emerald-700' : ($check['status'] === 'manual' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700') }}">{{ $check['status'] === 'passed' ? '✓' : ($check['status'] === 'manual' ? 'i' : '!') }}</span><div><p class="text-xs font-bold text-navy">{{ $check['label'] }}</p><p class="mt-0.5 text-[11px] leading-relaxed text-muted-foreground">{{ $check['message'] }}</p></div></div>
                    @endforeach
                </div>
                <p class="mt-4 text-[11px] leading-relaxed text-muted-foreground">Hasil ini adalah pemeriksaan awal berbasis teks. Kelolosan resmi tetap ditentukan oleh admin.</p>
            </div>
        @endif
        @if ($canUploadLetter)
            <form data-request-letter-form method="POST" action="{{ route('peserta.request-letter.store') }}" enctype="multipart/form-data" class="mt-5">@csrf<input type="file" name="request_letter" accept=".pdf" required class="block w-full rounded-xl border border-border bg-background p-3 text-sm">@error('request_letter')<p class="mt-2 text-xs font-semibold text-destructive">{{ $message }}</p>@enderror<label class="mt-4 flex items-start gap-3 text-xs leading-relaxed text-muted-foreground"><input type="checkbox" name="letter_declaration" value="1" required class="mt-0.5">Saya memastikan lokasi tujuan dan rentang tanggal sudah tercantum dalam surat.</label><button data-request-letter-submit class="mt-4 rounded-xl bg-ocean px-5 py-2.5 text-sm font-bold text-white transition disabled:pointer-events-none">{{ ($letterNeedsRevision || $automatedNeedsRevision) ? 'Unggah & Periksa Ulang' : 'Unggah & Periksa Surat' }}</button></form>
        @endif
    </article>
</section>

<section id="google-form" class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8 {{ $letterApproved ? '' : 'opacity-60' }}">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Tahap 4</p><h2 class="mt-2 text-2xl font-extrabold">Google Form resmi</h2><p class="mt-2 text-sm text-muted-foreground">Tahap ini terbuka setelah surat permohonan dinyatakan lolos oleh admin.</p></div>
        @if ($letterApproved && ! $application->google_form_confirmed_at)
            <div class="w-full max-w-xl rounded-2xl border border-ocean/10 bg-light/60 p-4 lg:w-auto">
                <p class="text-xs font-bold text-navy">Pilih formulir sesuai jenjang pendidikan Anda:</p>
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach ($application->googleFormOptions() as $label => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white transition hover:bg-ocean">
                            {{ $label }} <i data-lucide="external-link" class="h-4 w-4" aria-hidden="true"></i>
                        </a>
                    @endforeach
                    <form method="POST" action="{{ route('peserta.google-form.confirm') }}">@csrf<button class="rounded-xl border border-ocean bg-white px-5 py-3 text-sm font-bold text-ocean transition hover:bg-ocean hover:text-white">Saya sudah mengisi</button></form>
                </div>
            </div>
        @elseif($application->google_form_confirmed_at)<span class="rounded-full bg-teal/10 px-4 py-2 text-xs font-bold text-teal">Sudah dikonfirmasi</span>@else<span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-500">Menunggu surat lolos</span>@endif
    </div>
</section>

<section id="keputusan" class="grid gap-6 md:grid-cols-2">
    <article class="rounded-[2rem] border border-border bg-white p-6 shadow-sm transition sm:p-8 {{ $stageFiveUnlocked ? '' : 'pointer-events-none opacity-60' }}">
        <div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Tahap 5</p><h2 class="mt-2 text-xl font-extrabold">Surat balasan Dinas</h2></div>@unless($stageFiveUnlocked)<span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500"><i data-lucide="lock" class="h-4 w-4"></i></span>@endunless</div>
        @if (! $stageFiveUnlocked)<p class="mt-3 text-sm leading-relaxed text-muted-foreground">Selesaikan pengisian Google Form resmi pada Tahap 4 untuk membuka tahap ini.</p>@elseif($application->decision)<p class="mt-3 text-sm text-muted-foreground">Keputusan: <strong class="text-navy">{{ str($application->decision)->title() }}</strong></p>@else<p class="mt-3 text-sm leading-relaxed text-muted-foreground">Keputusan dan surat balasan akan tampil di sini serta dapat dikirim melalui email.</p>@endif
    </article>
    <article class="rounded-[2rem] border border-border bg-white p-6 shadow-sm transition sm:p-8 {{ $stageSixUnlocked ? '' : 'pointer-events-none opacity-60' }}">
        <div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-ocean">Tahap 6</p><h2 class="mt-2 text-xl font-extrabold">Kalender pelaksanaan</h2></div>@unless($stageSixUnlocked)<span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500"><i data-lucide="lock" class="h-4 w-4"></i></span>@endunless</div>
        @if (! $stageSixUnlocked)<p class="mt-3 text-sm leading-relaxed text-muted-foreground">Tahap ini terbuka setelah peserta dinyatakan diterima pada Tahap 5.</p>@elseif($officialStarted && $officialEnded)<div class="mt-5 rounded-2xl bg-navy p-5 text-white"><p class="text-3xl font-extrabold">{{ $daysRemaining }} hari</p><p class="mt-1 text-xs text-blue-200">tersisa hingga {{ $officialEnded->format('d M Y') }}</p><div class="mt-4 flex justify-between text-xs"><span>{{ $officialStarted->format('d M Y') }}</span><span>{{ $officialEnded->format('d M Y') }}</span></div></div>@else<p class="mt-3 text-sm leading-relaxed text-muted-foreground">Menunggu admin menetapkan tanggal mulai dan selesai resmi.</p>@endif
    </article>
</section>
