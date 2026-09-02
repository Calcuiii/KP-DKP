@php
    use App\Models\ParticipantApplicationDocument;

    $requestLetter = $application->latestDocument(ParticipantApplicationDocument::TYPE_REQUEST_LETTER);
    $letterApproved = $application->requestLetterApproved();
    $letterNeedsRevision = $requestLetter?->review_status === ParticipantApplicationDocument::REVIEW_REVISION;
    $automatedNeedsRevision = in_array($requestLetter?->automated_check_status, ['needs_revision', 'unreadable'], true);
    $canUploadLetter = ! $requestLetter || $letterNeedsRevision || $automatedNeedsRevision;
    $ethicsDocument = $application->latestDocument(ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL);
    $ethicsApproved = $application->ethicsApprovalApproved();
    $ethicsNeedsRevision = $ethicsDocument?->review_status === ParticipantApplicationDocument::REVIEW_REVISION;
    $ethicsAutomatedNeedsRevision = in_array($ethicsDocument?->automated_check_status, ['needs_revision', 'unreadable'], true);
    $canUploadEthics = ! $ethicsDocument || $ethicsNeedsRevision || $ethicsAutomatedNeedsRevision;
    $woppsFormProof = $application->latestDocument(ParticipantApplicationDocument::TYPE_WOPPS_FORM_PROOF);
    $woppsFormCompleted = $application->google_form_confirmed_at !== null && $woppsFormProof;
    $requirements = [
        ['user-round', 'Nama Mahasiswa'],
        ['badge', 'Nomor Induk Mahasiswa'],
        ['calendar-range', 'Semester'],
        ['graduation-cap', 'Program Studi / Departemen'],
        ['building-2', 'Fakultas'],
        ['school', 'Universitas'],
        ['contact', 'Dosen Pembimbing/Lapangan dan WhatsApp aktif'],
        ['calendar-clock', 'Batas Waktu / Deadline Keperluan Data'],
        ['database', 'Tujuan Penggunaan Data / Informasi'],
    ];
@endphp

<section id="persiapan" class="scroll-mt-28">
    <article class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-ocean">WOPPS · Tahap 1</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Pemeriksaan Surat Permohonan</h2>
                <p class="mt-3 text-sm leading-relaxed text-muted-foreground">Unggah surat permohonan resmi dari institusi pendidikan asal dalam format PDF maksimal 10 MB. Sistem akan memeriksa kelengkapan informasi sebelum surat ditinjau oleh admin.</p>
            </div>
            @if ($requestLetter)
                <span class="inline-flex w-fit rounded-full px-3 py-1.5 text-xs font-bold {{ ($letterNeedsRevision || $automatedNeedsRevision) ? 'bg-red-50 text-red-700' : ($letterApproved ? 'bg-teal/10 text-teal' : 'bg-amber-50 text-amber-700') }}">
                    {{ ($letterNeedsRevision || $automatedNeedsRevision) ? 'Perlu perbaikan' : ($letterApproved ? 'Lolos' : 'Menunggu admin') }}
                </span>
            @endif
        </div>

        <div class="mt-7 rounded-3xl border border-ocean/15 bg-gradient-to-br from-ocean/[0.05] to-teal/[0.05] p-5 sm:p-6">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-ocean text-white"><i data-lucide="list-checks" class="h-5 w-5"></i></span>
                <div><h3 class="text-base font-extrabold">Informasi yang wajib tercantum</h3><p class="mt-1 text-xs leading-relaxed text-muted-foreground">Gunakan daftar ini untuk memeriksa surat sebelum diunggah.</p></div>
            </div>
            <ol class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($requirements as $index => [$icon, $label])
                    <li class="rounded-2xl border border-ocean/10 bg-white/90 p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-ocean/25 hover:shadow-md">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-ocean/15 to-teal/15 text-ocean"><i data-lucide="{{ $icon }}" class="h-4 w-4"></i></span>
                            <span class="rounded-full bg-navy px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-white">{{ chr(97 + $index) }}</span>
                        </div>
                        <p class="mt-4 text-xs font-extrabold leading-relaxed text-navy">{{ $label }}</p>
                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Wajib tercantum</p>
                    </li>
                @endforeach
            </ol>
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
                <div class="mt-4 grid gap-2 md:grid-cols-2">
                    @foreach ($automatedResult['checks'] as $check)
                        <div class="rounded-xl border border-white/80 bg-white/80 p-3"><div class="flex items-start gap-2"><i data-lucide="{{ $check['status'] === 'passed' ? 'check-circle-2' : ($check['status'] === 'manual' ? 'info' : 'alert-circle') }}" class="mt-0.5 h-4 w-4 shrink-0 {{ $check['status'] === 'passed' ? 'text-teal' : ($check['status'] === 'manual' ? 'text-ocean' : 'text-red-600') }}"></i><div><p class="text-xs font-bold">{{ $check['label'] }}</p><p class="mt-0.5 text-[11px] leading-relaxed text-muted-foreground">{{ $check['message'] }}</p></div></div></div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($canUploadLetter)
            <form data-request-letter-form method="POST" action="{{ route('peserta.request-letter.store') }}" enctype="multipart/form-data" class="mt-6 rounded-2xl border border-dashed border-ocean/30 bg-background p-5">
                @csrf
                <label class="text-sm font-bold">Upload surat permohonan WOPPS</label>
                <input type="file" name="request_letter" accept=".pdf" required class="mt-3 block w-full rounded-xl border border-border bg-white p-3 text-sm">
                @error('request_letter')<p class="mt-2 text-xs font-semibold text-destructive">{{ $message }}</p>@enderror
                <label class="mt-4 flex items-start gap-3 text-xs leading-relaxed text-muted-foreground"><input type="checkbox" name="letter_declaration" value="1" required class="mt-0.5">Saya memastikan sembilan informasi wajib WOPPS sudah tercantum dalam surat.</label>
                <button data-request-letter-submit class="mt-4 inline-flex items-center gap-2 rounded-xl bg-ocean px-5 py-2.5 text-sm font-bold text-white transition disabled:pointer-events-none"><i data-lucide="scan-search" class="h-4 w-4"></i>{{ ($letterNeedsRevision || $automatedNeedsRevision) ? 'Unggah & Periksa Ulang' : 'Unggah & Periksa Surat' }}</button>
            </form>
        @else
            @if ($letterApproved)
                <div class="mt-5 flex items-start gap-3 rounded-2xl border border-teal/25 bg-teal/[0.06] p-4">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal text-white"><i data-lucide="check" class="h-4 w-4"></i></span>
                    <div>
                        <p class="text-sm font-bold text-teal">Surat permohonan telah disetujui admin</p>
                        <p class="mt-1 text-sm leading-relaxed text-muted-foreground">Silakan lanjutkan ke tahap berikutnya di bawah ini.</p>
                    </div>
                </div>
            @else
                <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">Surat sedang diperiksa admin. Unggah ulang akan tersedia jika admin meminta revisi.</div>
            @endif
        @endif
    </article>
</section>

@php ob_start(); @endphp
<section id="form-wopps" class="scroll-mt-28">
    <article class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8 {{ $ethicsApproved ? '' : 'opacity-60' }}">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
            <div class="max-w-3xl">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-ocean to-teal text-white"><i data-lucide="clipboard-check" class="h-5 w-5"></i></span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">WOPPS · Tahap 3</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight">Isi dan buktikan Form WOPPS</h2>
                    </div>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-muted-foreground">Setelah Ethics Approval disetujui, isi Form WOPPS resmi hingga selesai. Kemudian unggah screenshot halaman konfirmasi pengiriman sebagai bukti.</p>
            </div>
            @if (! $ethicsApproved)
                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600"><i data-lucide="lock" class="h-3.5 w-3.5"></i> Selesaikan Tahap 2</span>
            @elseif ($woppsFormCompleted)
                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-teal/10 px-3 py-1.5 text-xs font-bold text-teal"><i data-lucide="circle-check" class="h-3.5 w-3.5"></i> Bukti tersimpan</span>
            @endif
        </div>

        @if ($ethicsApproved)
            <div class="mt-7 grid gap-4 lg:grid-cols-[0.85fr_1.15fr]">
                <div class="rounded-3xl bg-gradient-to-br from-navy via-[#123d72] to-ocean p-6 text-white">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15"><i data-lucide="external-link" class="h-5 w-5"></i></span>
                    <h3 class="mt-5 text-lg font-extrabold">Form resmi WOPPS</h3>
                    <p class="mt-2 text-xs leading-relaxed text-blue-100">Form akan dibuka di tab baru. Pastikan seluruh data sudah benar sebelum menekan tombol kirim.</p>
                    <a href="https://bit.ly/WOPPS" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-ocean transition hover:-translate-y-0.5 hover:shadow-lg">
                        Buka Form WOPPS <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
                    </a>
                </div>

                <div class="rounded-3xl border border-ocean/15 bg-background p-5 sm:p-6">
                    @if ($woppsFormCompleted)
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal/10 text-teal"><i data-lucide="file-check-2" class="h-5 w-5"></i></span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-extrabold">Bukti pengisian sudah dikirim</h3>
                                <p class="mt-1 break-words text-xs text-muted-foreground">{{ $woppsFormProof->original_name }} · {{ $woppsFormProof->created_at->format('d M Y, H:i') }}</p>
                                <a href="{{ route('peserta.document.download', $woppsFormProof) }}" class="mt-3 inline-flex items-center gap-2 text-xs font-bold text-ocean"><i data-lucide="download" class="h-3.5 w-3.5"></i> Lihat bukti</a>
                            </div>
                        </div>
                        <div class="mt-5 rounded-2xl border border-teal/20 bg-teal/[0.06] p-4 text-xs font-semibold leading-relaxed text-teal">Tahap 3 selesai. Silakan menunggu tindak lanjut dari Dinas melalui portal.</div>
                    @else
                        <h3 class="text-sm font-extrabold">Upload bukti pengiriman</h3>
                        <p class="mt-1 text-xs leading-relaxed text-muted-foreground">Gunakan screenshot halaman yang menyatakan respons berhasil dikirim. Format JPG, PNG, atau PDF, maksimal 5 MB.</p>
                        <form method="POST" action="{{ route('peserta.wopps-form-proof.store') }}" enctype="multipart/form-data" class="mt-5">
                            @csrf
                            <input type="file" name="wopps_form_proof" accept=".jpg,.jpeg,.png,.pdf" required class="block w-full rounded-xl border border-border bg-white p-3 text-sm">
                            @error('wopps_form_proof')<p class="mt-2 text-xs font-semibold text-destructive">{{ $message }}</p>@enderror
                            <label class="mt-4 flex items-start gap-3 text-xs leading-relaxed text-muted-foreground"><input type="checkbox" name="wopps_form_declaration" value="1" required class="mt-0.5">Saya menyatakan telah mengisi dan mengirim Form WOPPS dengan data yang benar.</label>
                            @error('wopps_form_declaration')<p class="mt-2 text-xs font-semibold text-destructive">{{ $message }}</p>@enderror
                            <button type="submit" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-teal px-5 py-2.5 text-sm font-bold text-white transition hover:brightness-105"><i data-lucide="upload" class="h-4 w-4"></i> Simpan Bukti Pengisian</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </article>
</section>
@php $woppsStageThree = ob_get_clean(); @endphp

<section id="ethics-approval" class="scroll-mt-28">
    <article class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8 {{ $letterApproved ? '' : 'opacity-60' }}">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
            <div class="max-w-3xl">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-ocean to-teal text-white"><i data-lucide="shield-check" class="h-5 w-5"></i></span>
                    <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">WOPPS · Tahap 2</p><h2 class="mt-1 text-2xl font-extrabold tracking-tight">Pemeriksaan Ethics Approval Statement Letter</h2></div>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-muted-foreground">Isi template resmi dari Dinas, hapus pilihan atau petunjuk yang tidak digunakan, lalu unggah sebagai PDF maksimal 10 MB.</p>
            </div>
            @if (! $letterApproved)
                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600"><i data-lucide="lock" class="h-3.5 w-3.5"></i> Selesaikan Tahap 1</span>
            @elseif ($ethicsDocument)
                <span class="inline-flex w-fit rounded-full px-3 py-1.5 text-xs font-bold {{ ($ethicsNeedsRevision || $ethicsAutomatedNeedsRevision) ? 'bg-red-50 text-red-700' : ($ethicsApproved ? 'bg-teal/10 text-teal' : 'bg-amber-50 text-amber-700') }}">{{ ($ethicsNeedsRevision || $ethicsAutomatedNeedsRevision) ? 'Perlu perbaikan' : ($ethicsApproved ? 'Lolos' : 'Menunggu admin') }}</span>
            @endif
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['user-check', 'Identitas lengkap', 'Nama, NIS/NIM, program studi, fakultas, dan institusi.'],
                ['calendar-range', 'Detail kegiatan', 'Jenis, periode, lokasi, serta judul atau tema laporan.'],
                ['shield-check', 'Pernyataan etika', 'Empat komitmen kerahasiaan dan penggunaan data akademik.'],
                ['stamp', 'Pengesahan', 'Tanda tangan, meterai, pembimbing/dekan, NIP, dan stempel institusi.'],
            ] as [$icon, $title, $description])
                <div class="rounded-2xl border border-ocean/10 bg-light/50 p-4"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean/10 text-ocean"><i data-lucide="{{ $icon }}" class="h-4 w-4"></i></span><h3 class="mt-3 text-xs font-extrabold">{{ $title }}</h3><p class="mt-1 text-[11px] leading-relaxed text-muted-foreground">{{ $description }}</p></div>
            @endforeach
        </div>

        @if ($letterApproved)
            @if ($ethicsNeedsRevision)
                <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4"><p class="text-sm font-bold text-red-800">Dokumen perlu diperbaiki</p><p class="mt-1 text-sm leading-relaxed text-red-700">{{ $ethicsDocument->review_notes ?: 'Periksa kembali isian template dan unggah versi terbaru.' }}</p></div>
            @elseif ($ethicsDocument)
                <div class="mt-5 rounded-2xl border border-border bg-background p-4 text-sm"><p class="font-bold">Versi {{ $ethicsDocument->version }} · {{ $ethicsDocument->original_name }}</p><p class="mt-1 text-xs text-muted-foreground">Dikirim {{ $ethicsDocument->created_at->format('d M Y, H:i') }}</p><a href="{{ route('peserta.document.download', $ethicsDocument) }}" class="mt-3 inline-flex text-xs font-bold text-ocean">Unduh dokumen</a></div>
            @endif

            @if ($ethicsDocument?->automated_check_results)
                @php $ethicsResult = $ethicsDocument->automated_check_results; @endphp
                <div class="mt-5 rounded-2xl border {{ $ethicsAutomatedNeedsRevision ? 'border-red-200 bg-red-50/60' : 'border-teal/25 bg-teal/[0.05]' }} p-5">
                    <div class="flex items-start gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $ethicsAutomatedNeedsRevision ? 'bg-red-100 text-red-700' : 'bg-teal/10 text-teal' }}"><i data-lucide="scan-search" class="h-4 w-4"></i></span><div><p class="text-sm font-extrabold">Hasil pemeriksaan otomatis</p><p class="mt-1 text-xs leading-relaxed text-muted-foreground">{{ $ethicsResult['summary'] }}</p></div></div>
                    <div class="mt-4 grid gap-2 md:grid-cols-2">
                        @foreach ($ethicsResult['checks'] as $check)
                            <div class="rounded-xl border border-white/80 bg-white/80 p-3"><div class="flex items-start gap-2"><i data-lucide="{{ $check['status'] === 'passed' ? 'check-circle-2' : ($check['status'] === 'manual' ? 'info' : 'alert-circle') }}" class="mt-0.5 h-4 w-4 shrink-0 {{ $check['status'] === 'passed' ? 'text-teal' : ($check['status'] === 'manual' ? 'text-ocean' : 'text-red-600') }}"></i><div><p class="text-xs font-bold">{{ $check['label'] }}</p><p class="mt-0.5 text-[11px] leading-relaxed text-muted-foreground">{{ $check['message'] }}</p></div></div></div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($canUploadEthics)
                <form data-request-letter-form method="POST" action="{{ route('peserta.ethics-approval.store') }}" enctype="multipart/form-data" class="mt-6 rounded-2xl border border-dashed border-teal/35 bg-background p-5">
                    @csrf
                    <label class="text-sm font-bold">Upload Ethics Approval Statement Letter</label>
                    <input type="file" name="ethics_approval" accept=".pdf" required class="mt-3 block w-full rounded-xl border border-border bg-white p-3 text-sm">
                    @error('ethics_approval')<p class="mt-2 text-xs font-semibold text-destructive">{{ $message }}</p>@enderror
                    <label class="mt-4 flex items-start gap-3 text-xs leading-relaxed text-muted-foreground"><input type="checkbox" name="ethics_declaration" value="1" required class="mt-0.5">Saya memastikan template telah diisi lengkap dan pilihan yang tidak digunakan sudah dihapus.</label>
                    <button data-request-letter-submit class="mt-4 inline-flex items-center gap-2 rounded-xl bg-teal px-5 py-2.5 text-sm font-bold text-white"><i data-lucide="scan-search" class="h-4 w-4"></i>{{ ($ethicsNeedsRevision || $ethicsAutomatedNeedsRevision) ? 'Unggah & Periksa Ulang' : 'Unggah & Periksa Dokumen' }}</button>
                </form>
            @else
                @if ($ethicsApproved)
                    <div class="mt-5 flex items-start gap-3 rounded-2xl border border-teal/25 bg-teal/[0.06] p-4">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal text-white"><i data-lucide="check" class="h-4 w-4"></i></span>
                        <div>
                            <p class="text-sm font-bold text-teal">Ethics Approval Statement Letter telah disetujui admin</p>
                            <p class="mt-1 text-sm leading-relaxed text-muted-foreground">Silakan lanjutkan ke tahap berikutnya di bawah ini.</p>
                        </div>
                    </div>
                @else
                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">Dokumen sedang diperiksa admin. Unggah ulang tersedia jika admin meminta revisi.</div>
                @endif
            @endif
        @endif
    </article>
</section>

{!! $woppsStageThree !!}

<section id="tindak-lanjut-wopps" class="scroll-mt-28">
    <article class="relative overflow-hidden rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8 {{ $woppsFormCompleted ? '' : 'opacity-60' }}">
        <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-teal/[0.07]"></div>
        <div class="relative flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="max-w-3xl">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-teal to-ocean text-white"><i data-lucide="message-circle" class="h-5 w-5"></i></span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">WOPPS · Tahap 4</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight">Koordinasi tindak lanjut</h2>
                    </div>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-muted-foreground">Setelah Form WOPPS dikirim, silakan melakukan koordinasi lebih lanjut dengan pihak Dinas Kelautan dan Perikanan Provinsi Jawa Timur melalui kontak resmi berikut.</p>
            </div>

            @if (! $woppsFormCompleted)
                <span class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600"><i data-lucide="lock" class="h-3.5 w-3.5"></i> Selesaikan Tahap 3</span>
            @endif
        </div>

        @if ($woppsFormCompleted)
            <div class="relative mt-7 flex flex-col gap-5 rounded-3xl border border-teal/20 bg-gradient-to-br from-teal/[0.07] via-white to-ocean/[0.06] p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-teal text-white shadow-lg shadow-teal/20"><i data-lucide="contact" class="h-5 w-5"></i></span>
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-teal">Kontak Koordinasi WOPPS</p>
                        <h3 class="mt-1 text-base font-extrabold text-navy">Bapak Dicky Fadillah</h3>
                        <p class="mt-1 text-sm font-semibold text-muted-foreground">+62 852-5300-0485</p>
                    </div>
                </div>
                <a href="https://wa.me/6285253000485" target="_blank" rel="noopener noreferrer" class="inline-flex w-fit shrink-0 items-center gap-2 rounded-xl bg-teal px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-teal/15 transition hover:-translate-y-0.5 hover:brightness-105">
                    <i data-lucide="message-circle" class="h-4 w-4"></i> Hubungi via WhatsApp
                </a>
            </div>
            <p class="relative mt-4 flex items-start gap-2 text-xs leading-relaxed text-muted-foreground"><i data-lucide="info" class="mt-0.5 h-4 w-4 shrink-0 text-ocean"></i>Sampaikan identitas, asal institusi, serta keperluan WOPPS secara ringkas agar koordinasi dapat diproses dengan baik.</p>
        @endif
    </article>
</section>