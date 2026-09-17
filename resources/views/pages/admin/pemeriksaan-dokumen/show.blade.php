@extends('layouts.admin')

@php
    use App\Models\ParticipantApplicationDocument;

    $automatedStatus = $document->automated_check_status;

    /*
    |--------------------------------------------------------------------------
    | STATUS PEMERIKSAAN OTOMATIS
    |--------------------------------------------------------------------------
    */

    $automatedClass = match ($automatedStatus) {
        'passed' =>
            'bg-emerald-50 text-emerald-700 border-emerald-200',

        'needs_revision',
        'unreadable' =>
            'bg-red-50 text-red-700 border-red-200',

        default =>
            'bg-amber-50 text-amber-700 border-amber-200',
    };

    $automatedLabel = match ($automatedStatus) {
        'passed' =>
            'Lolos pemeriksaan otomatis',

        'needs_revision' =>
            'Perlu perbaikan',

        'unreadable' =>
            'Dokumen tidak terbaca',

        default =>
            'Menunggu pemeriksaan otomatis',
    };


    /*
    |--------------------------------------------------------------------------
    | STATUS PROSES ADMIN
    |--------------------------------------------------------------------------
    */

    $processClass = match (true) {
        $document->review_status === ParticipantApplicationDocument::REVIEW_APPROVED =>
            'bg-emerald-50 text-emerald-700 border-emerald-200',

        $document->review_status === ParticipantApplicationDocument::REVIEW_REVISION =>
            'bg-red-50 text-red-700 border-red-200',

        $automatedStatus === 'passed'
            && $document->review_status === ParticipantApplicationDocument::REVIEW_SUBMITTED =>
            'bg-blue-50 text-blue-700 border-blue-200',

        default =>
            'bg-amber-50 text-amber-700 border-amber-200',
    };


    $processLabel = match (true) {
        $document->review_status === ParticipantApplicationDocument::REVIEW_APPROVED =>
            'Disetujui admin',

        $document->review_status === ParticipantApplicationDocument::REVIEW_REVISION =>
            'Menunggu perbaikan peserta',

        $automatedStatus === 'passed'
            && $document->review_status === ParticipantApplicationDocument::REVIEW_SUBMITTED =>
            'Siap diperiksa admin',

        $automatedStatus !== 'passed' =>
            'Menunggu perbaikan peserta',

        default =>
            'Menunggu proses',
    };


    /*
    |--------------------------------------------------------------------------
    | HASIL PEMERIKSAAN OTOMATIS
    |--------------------------------------------------------------------------
    */

    $result = $document->automated_check_results ?? [];
@endphp


@section('content')

<div class="space-y-6">

    {{-- =========================================================
         KELOMPOK SURAT BERSAMA
    ========================================================== --}}
    @if ($relatedLetters->isNotEmpty())

        <section class="rounded-xl border border-border bg-white p-5">

            <h2 class="font-bold text-navy">
                Kelompok surat bersama
            </h2>

            <p class="mt-1">
                {{ $document->letter_institution }} — file surat identik
            </p>

            <p class="mt-2 text-sm">
                {{ $relatedLetters->count() }} pengajuan;
                {{ $relatedLetters->where('application.decision', 'accepted')->count() }}
                peserta diterima.

                Satu peserta diterima dihitung satu tempat.
                Kuota lokasi tetap dikelola melalui menu kuota.
            </p>

            <p class="mt-2 text-sm">
                Cocokkan nama dan NIM/NIS setiap peserta pada lampiran.
                Jika tidak tercantum, minta perbaikan.
                Keputusan hanya berlaku untuk peserta yang sedang diperiksa.
            </p>

            <ul class="mt-3 space-y-2">

                @foreach ($relatedLetters as $related)

                    <li>
                        <a
                            class="text-ocean underline"
                            href="{{ route('admin.pemeriksaan-dokumen.show', $related) }}"
                        >
                            {{ $related->application->participant->name }}
                            —
                            {{ $related->application->application_code }}
                        </a>

                        · {{ $related->application->status }}
                    </li>

                @endforeach

            </ul>

        </section>

    @endif


    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div>

        <a
            href="{{ route('admin.pemeriksaan-dokumen') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-ocean hover:underline"
        >
            ← Kembali ke daftar dokumen
        </a>

        <div class="mt-5">

            <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">
                Pemeriksaan Dokumen
            </p>

            <h1 class="mt-1 text-2xl font-extrabold text-navy">
                Verifikasi Surat Permohonan
            </h1>

            <p class="mt-2 text-sm text-muted-foreground">
                Periksa dokumen dan tentukan keputusan akhir administrasi.
            </p>

        </div>

    </div>


    {{-- =========================================================
         SUCCESS MESSAGE
    ========================================================== --}}
    @if (session('success'))

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>

    @endif


    {{-- =========================================================
         LAYOUT UTAMA
         KIRI  : Dokumen + Informasi + Pemeriksaan Otomatis
         KANAN : Keputusan Admin
    ========================================================== --}}
    <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">


        {{-- =====================================================
             BAGIAN KIRI
        ====================================================== --}}
        <section class="space-y-5">





            {{-- =================================================
                 INFORMASI PESERTA
            ================================================== --}}
            <article class="rounded-2xl border border-border bg-white p-6 shadow-sm">

                <p class="text-xs font-bold uppercase tracking-[0.15em] text-teal">
                    Informasi Peserta
                </p>

                <div class="mt-4 grid gap-3 sm:grid-cols-1">


                    {{-- Nama --}}
                    <div class="rounded-xl bg-light/90 p-4">

                        <p class="text-xs text-muted-foreground">
                            Nama Peserta
                        </p>

                        <p class="mt-1 font-bold text-navy">
                            {{ $document->application?->participant?->name ?? '-' }}
                        </p>

                        <p class="text-xs text-muted-foreground">
                            Email
                        </p>

                        <p class="mt-1 break-all font-bold text-navy">
                            {{ $document->application?->participant?->email ?? '-' }}
                        </p>

                    </div>

                </div>

            </article>
            {{-- =================================================
                 DOKUMEN PESERTA
            ================================================== --}}
            <article class="rounded-2xl border border-border bg-white p-6 shadow-sm">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-ocean">
                            Dokumen Peserta
                        </p>

                        <h2 class="mt-2 truncate text-lg font-extrabold text-navy">
                            {{ $document->original_name }}
                        </h2>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Versi {{ $document->version }}
                            ·
                            {{ $document->created_at->format('d M Y, H:i') }}
                        </p>

                    </div>


                    <span class="shrink-0 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-600">
                        PDF
                    </span>

                </div>


                {{-- Tombol Preview --}}
                <div class="mt-5">

                    <button
                        type="button"
                        onclick="openDocumentPreview()"
                        class="inline-flex items-center gap-2 rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white transition hover:bg-ocean"
                    >
                        <i data-lucide="file-text" class="h-4 w-4"></i>

                        Buka Dokumen
                    </button>

                </div>

            </article>
        </section>

            {{-- =================================================
                 HASIL PEMERIKSAAN OTOMATIS
            ================================================== --}}






        {{-- =====================================================
             BAGIAN KANAN
             KEPUTUSAN ADMIN
        ====================================================== --}}
        <aside>

            <div class="sticky top-6 rounded-2xl border border-border bg-white p-5 shadow-sm">


                {{-- =================================================
                     HEADER KEPUTUSAN
                ================================================== --}}
                <div>

                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-teal">
                        Keputusan Admin
                    </p>

                    <h2 class="mt-1 text-lg font-extrabold text-navy">
                        Tentukan keputusan
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Pastikan dokumen asli sudah diperiksa.
                    </p>

                </div>


                {{-- =================================================
                     BELUM LOLOS OTOMATIS
                ================================================== --}}
                @if ($automatedStatus !== 'passed')

                    <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">

                        <p class="text-sm font-bold text-amber-800">
                            Belum dapat diperiksa admin
                        </p>

                        <p class="mt-1 text-xs leading-relaxed text-amber-700">
                            Peserta perlu memperbaiki dokumen terlebih dahulu.
                        </p>

                    </div>


                {{-- =================================================
                     SUDAH DISETUJUI
                ================================================== --}}
                @elseif ($document->review_status === ParticipantApplicationDocument::REVIEW_APPROVED)

                    <div
                        class="mt-5 rounded-xl border p-4
                        {{ $document->certificate_eligible === false
                            ? 'border-amber-200 bg-amber-50'
                            : 'border-emerald-200 bg-emerald-50' }}"
                    >

                        <p
                            class="text-sm font-bold
                            {{ $document->certificate_eligible === false
                                ? 'text-amber-800'
                                : 'text-emerald-800' }}"
                        >
                            ✓ Dokumen telah disetujui
                        </p>


                        <p
                            class="mt-1 text-xs
                            {{ $document->certificate_eligible === false
                                ? 'text-amber-700'
                                : 'text-emerald-700' }}"
                        >

                            @if ($document->certificate_eligible === false)

                                Pemeriksaan administrasi selesai.

                                Peserta diterima tanpa pernyataan permohonan sertifikat
                                dan akan diminta menentukan tindak lanjut.

                            @else

                                Pemeriksaan administrasi telah selesai.

                            @endif

                        </p>

                    </div>


                    {{-- Catatan Admin --}}
                    @if ($document->review_notes)

                        <div class="mt-4">

                            <p class="text-xs font-bold text-navy">
                                Catatan admin
                            </p>

                            <div class="mt-2 whitespace-pre-line rounded-xl bg-light/60 p-4 text-sm text-muted-foreground">
                                {{ $document->review_notes }}
                            </div>

                        </div>

                    @endif


                {{-- =================================================
                     MENUNGGU PERBAIKAN
                ================================================== --}}
                @elseif ($document->review_status === ParticipantApplicationDocument::REVIEW_REVISION)

                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4">

                        <p class="text-sm font-bold text-red-800">
                            Menunggu perbaikan peserta
                        </p>

                        <p class="mt-1 text-xs leading-relaxed text-red-700">
                            Tunggu peserta mengunggah dokumen yang diperbaiki.
                        </p>

                    </div>


                    {{-- Alasan Perbaikan --}}
                    @if ($document->review_notes)

                        <div class="mt-4">

                            <p class="text-xs font-bold text-navy">
                                Alasan perbaikan
                            </p>

                            <div class="mt-2 whitespace-pre-line rounded-xl bg-light/60 p-4 text-sm text-muted-foreground">
                                {{ $document->review_notes }}
                            </div>

                        </div>

                    @endif


                {{-- =================================================
                     SIAP DIPERIKSA ADMIN
                ================================================== --}}
                @else

                    {{-- =================================================
                         DUA KEPUTUSAN ADMIN
                         1. SETUJUI SURAT
                         2. MINTA PERBAIKAN
                    ================================================== --}}
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">


                        {{-- =================================================
                             SETUJUI SURAT
                        ================================================== --}}
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">

                            <p class="text-sm font-bold text-emerald-800">
                                Dokumen sudah benar?
                            </p>

                            <p class="mt-1 text-xs leading-relaxed text-emerald-700">
                                Setujui jika dokumen sudah lengkap dan sesuai.
                            </p>


                            <form
                                method="POST"
                                action="{{ route('admin.pemeriksaan-dokumen.approve', $document) }}"
                                class="mt-4"
                            >

                                @csrf
                                @method('PATCH')


                                {{-- Konfirmasi Identitas --}}
                                @if ($document->letter_group_key)

                                    <label class="flex items-start gap-2 rounded-lg border border-emerald-200 bg-white p-3 text-xs leading-relaxed">

                                        <input
                                            type="checkbox"
                                            name="participant_identity_confirmed"
                                            value="1"
                                            required
                                            class="mt-0.5 h-4 w-4 shrink-0"
                                        >

                                        <span>
                                            Saya sudah mencocokkan nama dan NIM/NIS peserta ini pada surat/lampiran.
                                        </span>

                                    </label>


                                    @error('participant_identity_confirmed')

                                        <p class="mt-1 text-xs font-semibold text-destructive">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                @endif


                                {{-- Tanpa Sertifikat --}}
                                <label class="mt-3 flex items-start gap-2 rounded-lg border border-amber-300 bg-amber-50 p-3 text-xs leading-relaxed text-amber-800">

                                    <input
                                        type="checkbox"
                                        name="no_certificate"
                                        value="1"
                                        class="mt-0.5 h-4 w-4 shrink-0"
                                    >

                                    <span>

                                        <span class="font-bold">
                                            Diterima tanpa sertifikat
                                        </span>

                                        <span class="mt-1 block">
                                            Surat tidak mencantumkan keterangan
                                            permintaan penerbitan sertifikat.
                                            Peserta tetap dapat melanjutkan proses
                                            dan akan diminta memilih upload ulang
                                            atau lanjut tanpa upload ulang.
                                        </span>

                                    </span>

                                </label>


                                {{-- Catatan --}}
                                <textarea
                                    name="review_notes"
                                    rows="3"
                                    class="mt-3 w-full rounded-lg border border-emerald-200 bg-white p-3 text-xs outline-none focus:border-emerald-400"
                                    placeholder="Catatan tambahan (opsional)"
                                >{{ old('review_notes') }}</textarea>


                                {{-- Tombol Setujui --}}
                                <button
                                    type="submit"
                                    class="mt-3 w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-700"
                                    onclick="return confirm('Apakah dokumen ini sudah benar dan ingin disetujui?')"
                                >
                                    ✓ Setujui Surat
                                </button>

                            </form>

                        </div>


                        {{-- =================================================
                             MINTA PERBAIKAN
                        ================================================== --}}
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">

                            <p class="text-sm font-bold text-red-800">
                                Masih ada kekurangan?
                            </p>

                            <p class="mt-1 text-xs leading-relaxed text-red-700">
                                Jelaskan bagian yang perlu diperbaiki.
                            </p>


                            <form
                                method="POST"
                                action="{{ route('admin.pemeriksaan-dokumen.revision', $document) }}"
                                class="mt-4"
                            >

                                @csrf
                                @method('PATCH')


                                {{-- Catatan Perbaikan --}}
                                <textarea
                                    name="review_notes"
                                    rows="5"
                                    required
                                    class="w-full rounded-lg border border-red-200 bg-white p-3 text-xs outline-none focus:border-red-400"
                                    placeholder="Contoh: Lokasi belum lengkap, tanda tangan belum tersedia, atau data peserta belum sesuai..."
                                >{{ old('review_notes') }}</textarea>


                                @error('review_notes')

                                    <p class="mt-1.5 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>

                                @enderror


                                {{-- Tombol Minta Perbaikan --}}
                                <button
                                    type="submit"
                                    class="mt-3 w-full rounded-lg bg-red-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-red-700"
                                    onclick="return confirm('Kirim permintaan perbaikan kepada peserta?')"
                                >
                                    ↻ Minta Perbaikan
                                </button>

                            </form>

                        </div>

                    </div>

                @endif

            </div>

        </aside>

    </div>


    {{-- =========================================================
         MODAL PREVIEW DOKUMEN
         DILETAKKAN DI LUAR LAYOUT UTAMA
    ========================================================== --}}
    <div
        id="documentPreviewModal"
        class="fixed inset-0 z-[9999] hidden"
        aria-hidden="true"
    >

        {{-- Overlay --}}
        <div
            class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"
            onclick="closeDocumentPreview()"
        ></div>


        {{-- Container Modal --}}
        <div class="relative flex h-full w-full items-center justify-center p-4 sm:p-6">

            <div
                class="relative flex h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            >


                {{-- =================================================
                     HEADER MODAL
                ================================================== --}}
                <div class="flex shrink-0 items-center justify-between border-b border-border bg-white px-5 py-4">

                    <div class="min-w-0">

                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-teal">
                            Preview Dokumen
                        </p>

                        <h2 class="mt-1 truncate text-base font-extrabold text-navy sm:text-lg">
                            {{ $document->original_name }}
                        </h2>

                    </div>


                    {{-- Tombol X --}}
                    <button
                        type="button"
                        onclick="closeDocumentPreview()"
                        class="ml-4 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition hover:bg-slate-200 hover:text-slate-900"
                        aria-label="Tutup preview"
                    >
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>

                </div>


                {{-- =================================================
                     ISI PDF
                ================================================== --}}
                <div class="min-h-0 flex-1 bg-slate-100 p-2 sm:p-4">

                    <iframe
                        id="documentPreviewFrame"
                        src="{{ route('admin.pemeriksaan-dokumen.preview', $document) }}"
                        class="h-full w-full rounded-xl border border-slate-200 bg-white"
                        title="Preview {{ $document->original_name }}"
                    ></iframe>

                </div>


                {{-- =================================================
                     FOOTER MODAL
                ================================================== --}}
                <div class="flex shrink-0 flex-col gap-3 border-t border-border bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                    <p class="text-xs text-muted-foreground">
                        Periksa isi surat sebelum menentukan keputusan administrasi.
                    </p>


                    <div class="flex items-center gap-2">

                        {{-- Download --}}
                        <a
                            href="{{ route('admin.pemeriksaan-dokumen.download', $document) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-navy px-5 py-2.5 text-sm font-bold text-white transition hover:bg-ocean"
                        >
                            <i data-lucide="download" class="h-4 w-4"></i>

                            Download Dokumen
                        </a>


                        {{-- Tutup --}}
                        <button
                            type="button"
                            onclick="closeDocumentPreview()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-white px-5 py-2.5 text-sm font-bold text-navy transition hover:bg-slate-50"
                        >
                            Tutup
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     JAVASCRIPT MODAL
========================================================== --}}
<script>
    function openDocumentPreview() {
        const modal = document.getElementById('documentPreviewModal');

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('overflow-hidden');

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }


    function closeDocumentPreview() {
        const modal = document.getElementById('documentPreviewModal');

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');

        document.body.classList.remove('overflow-hidden');
    }


    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {
            closeDocumentPreview();
        }

    });
</script>

@endsection