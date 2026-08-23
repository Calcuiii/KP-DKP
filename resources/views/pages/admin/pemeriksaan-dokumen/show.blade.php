@extends('layouts.admin')

@php
    use App\Models\ParticipantApplicationDocument;

    $automatedStatus = $document->automated_check_status;

    /*
    |--------------------------------------------------------------------------
    | Status Pemeriksaan Otomatis
    |--------------------------------------------------------------------------
    */

    $automatedClass = match($automatedStatus) {
        'passed' =>
            'bg-emerald-50 text-emerald-700 border-emerald-200',

        'needs_revision',
        'unreadable' =>
            'bg-red-50 text-red-700 border-red-200',

        default =>
            'bg-amber-50 text-amber-700 border-amber-200',
    };

    $automatedLabel = match($automatedStatus) {
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
    | Status Proses Admin
    |--------------------------------------------------------------------------
    */

    $processClass = match(true) {

        $document->review_status === ParticipantApplicationDocument::REVIEW_APPROVED =>
            'bg-emerald-50 text-emerald-700 border-emerald-200',

        $document->review_status === ParticipantApplicationDocument::REVIEW_REVISION =>
            'bg-red-50 text-red-700 border-red-200',

        $automatedStatus === 'passed' &&
        $document->review_status === ParticipantApplicationDocument::REVIEW_SUBMITTED =>
            'bg-blue-50 text-blue-700 border-blue-200',

        default =>
            'bg-amber-50 text-amber-700 border-amber-200',
    };


    $processLabel = match(true) {

        $document->review_status === ParticipantApplicationDocument::REVIEW_APPROVED =>
            'Disetujui admin',

        $document->review_status === ParticipantApplicationDocument::REVIEW_REVISION =>
            'Menunggu perbaikan peserta',

        $automatedStatus === 'passed' &&
        $document->review_status === ParticipantApplicationDocument::REVIEW_SUBMITTED =>
            'Siap diperiksa admin',

        $automatedStatus !== 'passed' =>
            'Menunggu perbaikan peserta',

        default =>
            'Menunggu proses',
    };


    /*
    |--------------------------------------------------------------------------
    | Hasil Pemeriksaan Otomatis
    |--------------------------------------------------------------------------
    */

    $result = $document->automated_check_results ?? [];

@endphp


@section('content')

<div class="space-y-6">

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
    @if(session('success'))

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">

            {{ session('success') }}

        </div>

    @endif


    {{-- =========================================================
         LAYOUT UTAMA
    ========================================================== --}}
    <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">


        {{-- =====================================================
             BAGIAN KIRI
        ====================================================== --}}
        <section class="space-y-5">


            {{-- =================================================
                 DOKUMEN
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


                <a
                    href="{{ route('admin.pemeriksaan-dokumen.download', $document) }}"
                    target="_blank"
                    class="mt-5 inline-flex items-center gap-2 rounded-xl bg-navy px-5 py-2.5 text-sm font-bold text-white hover:opacity-90"
                >
                    <i data-lucide="file-text" class="h-4 w-4"></i>
                    Buka Dokumen
                </a>

            </article>


            {{-- =================================================
                 INFORMASI PESERTA
            ================================================== --}}
            <article class="rounded-2xl border border-border bg-white p-6 shadow-sm">

                <p class="text-xs font-bold uppercase tracking-[0.15em] text-teal">
                    Informasi Peserta
                </p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">

                    <div class="rounded-xl bg-light/60 p-4">

                        <p class="text-xs text-muted-foreground">
                            Nama Peserta
                        </p>

                        <p class="mt-1 font-bold text-navy">
                            {{ $document->application?->participant?->name ?? '-' }}
                        </p>

                    </div>


                    <div class="rounded-xl bg-light/60 p-4">

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
                 HASIL PEMERIKSAAN OTOMATIS
            ================================================== --}}
            @if($document->automated_check_results)

                <article class="rounded-2xl border border-border bg-white p-6 shadow-sm">

                    <div class="flex items-center justify-between gap-4">

                        <div>

                            <p class="text-xs font-bold uppercase tracking-[0.15em] text-ocean">
                                Pemeriksaan Otomatis
                            </p>

                            <h2 class="mt-1 text-lg font-extrabold text-navy">
                                Hasil Analisis Sistem
                            </h2>

                        </div>


                        <span
                            class="inline-flex shrink-0 rounded-full border px-3 py-1 text-xs font-bold {{ $automatedClass }}"
                        >
                            {{ $automatedLabel }}
                        </span>

                    </div>


                    {{-- Summary --}}
                    @if(!empty($result['summary']))

                        <div class="mt-4 rounded-xl bg-light/60 p-4">

                            <p class="text-sm leading-relaxed text-muted-foreground">
                                {{ $result['summary'] }}
                            </p>

                        </div>

                    @endif


                    {{-- Check List --}}
                    @if(!empty($result['checks']))

                        <div class="mt-4 divide-y divide-border rounded-xl border border-border">

                            @foreach($result['checks'] as $check)

                                @php

                                    $checkStatus = $check['status'] ?? 'failed';

                                    $checkClass = match($checkStatus) {

                                        'passed' =>
                                            'bg-emerald-100 text-emerald-700',

                                        'manual' =>
                                            'bg-blue-100 text-blue-700',

                                        default =>
                                            'bg-red-100 text-red-700',
                                    };


                                    $checkIcon = match($checkStatus) {

                                        'passed' =>
                                            '✓',

                                        'manual' =>
                                            'i',

                                        default =>
                                            '!',
                                    };

                                @endphp


                                <div class="flex items-start gap-3 p-4">

                                    <span
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $checkClass }}"
                                    >
                                        {{ $checkIcon }}
                                    </span>


                                    <div class="min-w-0">

                                        <p class="text-sm font-bold text-navy">
                                            {{ $check['label'] ?? 'Pemeriksaan' }}
                                        </p>

                                        @if(!empty($check['message']))

                                            <p class="mt-1 text-xs leading-relaxed text-muted-foreground">
                                                {{ $check['message'] }}
                                            </p>

                                        @endif

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endif


                    {{-- Catatan --}}
                    <div class="mt-4 rounded-xl bg-blue-50 px-4 py-3 text-xs leading-relaxed text-blue-700">

                        <strong>Penting:</strong>
                        hasil otomatis hanya merupakan pemeriksaan awal.
                        Keputusan akhir tetap dilakukan oleh admin.

                    </div>

                </article>

            @endif

        </section>


                {{-- =================================================
            KEPUTUSAN ADMIN
        ================================================== --}}
        <aside>

            <div class="sticky top-6 rounded-2xl border border-border bg-white p-6 shadow-sm">

                {{-- Header --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-teal">
                        Keputusan Admin
                    </p>

                    <h2 class="mt-1 text-xl font-extrabold text-navy">
                        Tentukan keputusan
                    </h2>

                    <p class="mt-2 text-sm text-muted-foreground">
                        Pastikan dokumen asli sudah diperiksa.
                    </p>
                </div>


                {{-- Jika belum lolos otomatis --}}
                @if($automatedStatus !== 'passed')

                    <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">

                        <p class="text-sm font-bold text-amber-800">
                            Belum dapat diperiksa admin
                        </p>

                        <p class="mt-1 text-xs leading-relaxed text-amber-700">
                            Peserta perlu memperbaiki dokumen terlebih dahulu.
                        </p>

                    </div>


                {{-- Jika sudah disetujui --}}
                @elseif($document->review_status === ParticipantApplicationDocument::REVIEW_APPROVED)

                    <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">

                        <p class="text-sm font-bold text-emerald-800">
                            ✓ Dokumen telah disetujui
                        </p>

                        <p class="mt-1 text-xs text-emerald-700">
                            Pemeriksaan administrasi telah selesai.
                        </p>

                    </div>


                {{-- Jika sedang menunggu perbaikan peserta --}}
                @elseif($document->review_status === ParticipantApplicationDocument::REVIEW_REVISION)

                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4">

                        <p class="text-sm font-bold text-red-800">
                            Menunggu perbaikan peserta
                        </p>

                        <p class="mt-1 text-xs leading-relaxed text-red-700">
                            Tunggu peserta mengunggah dokumen yang diperbaiki.
                        </p>

                    </div>


                    @if($document->review_notes)

                        <div class="mt-4">

                            <p class="text-xs font-bold text-navy">
                                Alasan perbaikan
                            </p>

                            <div class="mt-2 rounded-xl bg-light/60 p-4 text-sm text-muted-foreground">
                                {{ $document->review_notes }}
                            </div>

                        </div>

                    @endif


                {{-- =================================================
                    SIAP DIPERIKSA ADMIN
                ================================================== --}}
                @else

                    {{-- SETUJUI --}}
                    <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">

                        <p class="font-bold text-emerald-800">
                            Dokumen sudah benar?
                        </p>

                        <p class="mt-1 text-xs text-emerald-700">
                            Jika lengkap dan sesuai, setujui dokumen.
                        </p>


                        <form
                            method="POST"
                            action="{{ route('admin.pemeriksaan-dokumen.approve', $document) }}"
                            class="mt-4"
                        >

                            @csrf
                            @method('PATCH')

                            <textarea
                                name="review_notes"
                                rows="2"
                                class="w-full rounded-xl border border-emerald-200 bg-white p-3 text-sm outline-none focus:border-emerald-400"
                                placeholder="Catatan persetujuan (opsional)"
                            >{{ old('review_notes') }}</textarea>


                            <button
                                type="submit"
                                class="mt-3 w-full rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700"
                                onclick="return confirm('Apakah dokumen ini sudah benar dan ingin disetujui?')"
                            >
                                ✓ Setujui Dokumen
                            </button>

                        </form>

                    </div>


                    {{-- MINTA PERBAIKAN --}}
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4">

                        <p class="font-bold text-red-800">
                            Masih ada kekurangan?
                        </p>

                        <p class="mt-1 text-xs text-red-700">
                            Jelaskan bagian yang perlu diperbaiki peserta.
                        </p>


                        <form
                            method="POST"
                            action="{{ route('admin.pemeriksaan-dokumen.revision', $document) }}"
                            class="mt-4"
                        >

                            @csrf
                            @method('PATCH')

                            <textarea
                                name="review_notes"
                                rows="3"
                                required
                                class="w-full rounded-xl border border-red-200 bg-white p-3 text-sm outline-none focus:border-red-400"
                                placeholder="Contoh: Lokasi kegiatan belum lengkap..."
                            >{{ old('review_notes') }}</textarea>


                            @error('review_notes')

                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror


                            <button
                                type="submit"
                                class="mt-3 w-full rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white hover:bg-red-700"
                                onclick="return confirm('Kirim permintaan perbaikan kepada peserta?')"
                            >
                                ↻ Minta Perbaikan
                            </button>

                        </form>

                    </div>

                @endif

            </div>

        </aside>


    </div>

</div>

@endsection