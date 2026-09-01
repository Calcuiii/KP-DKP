@extends('layouts.admin')

@php
    use App\Models\ParticipantApplicationDocument;
@endphp

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">
                Verifikasi Administrasi
            </p>

            <h1 class="mt-1 text-2xl font-extrabold text-navy">
                Pemeriksaan Dokumen
            </h1>

            <p class="mt-2 text-sm text-muted-foreground">
                Periksa hasil analisis otomatis dan tentukan keputusan akhir surat permohonan peserta.
            </p>
        </div>
    </div>


    {{-- Flash message --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif


    {{-- Daftar --}}
    <div class="overflow-hidden rounded-[2rem] border border-border bg-white shadow-sm">

        <div class="border-b border-border p-5 sm:p-6">
            <h2 class="text-lg font-extrabold text-navy">
                Surat Permohonan Peserta
            </h2>

            <p class="mt-1 text-sm text-muted-foreground">
                Dokumen peserta beserta hasil pemeriksaan otomatis dan status proses pemeriksaan administrasi.
            </p>
        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-light/60 text-left">
                    <tr>
                        <th class="px-5 py-4 font-bold text-navy">
                            Peserta
                        </th>

                        <th class="px-5 py-4 font-bold text-navy">
                            Dokumen
                        </th>

                        <th class="px-5 py-4 font-bold text-navy">
                            Pemeriksaan Otomatis
                        </th>

                        <th class="px-5 py-4 font-bold text-navy">
                            Status Proses
                        </th>

                        <th class="px-5 py-4 font-bold text-navy">
                            Aksi
                        </th>
                    </tr>
                </thead>


                <tbody class="divide-y divide-border">

                    @forelse($documents as $document)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | STATUS PEMERIKSAAN OTOMATIS
                            |--------------------------------------------------------------------------
                            */

                            $automatedStatus = $document->automated_check_status;

                            $automatedClass = match($automatedStatus) {

                                'passed' =>
                                    'bg-emerald-50 text-emerald-700',

                                'needs_revision',
                                'unreadable' =>
                                    'bg-red-50 text-red-700',

                                default =>
                                    'bg-amber-50 text-amber-700',
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
                            | STATUS PROSES
                            |--------------------------------------------------------------------------
                            |
                            | Status proses mempertimbangkan:
                            | 1. Hasil pemeriksaan otomatis
                            | 2. Status pemeriksaan admin
                            |
                            */


                            $reviewClass = match(true) {

                                /*
                                |--------------------------------------------------------------------------
                                | Pemeriksaan otomatis belum lolos
                                |--------------------------------------------------------------------------
                                |
                                | Dokumen belum menjadi pekerjaan admin.
                                | Peserta harus memperbaiki dokumen terlebih dahulu.
                                |
                                */

                                $automatedStatus !== 'passed' =>
                                    'bg-red-50 text-red-700',


                                /*
                                |--------------------------------------------------------------------------
                                | Sudah disetujui admin
                                |--------------------------------------------------------------------------
                                */

                                $document->review_status === ParticipantApplicationDocument::REVIEW_APPROVED =>
                                    'bg-emerald-50 text-emerald-700',


                                /*
                                |--------------------------------------------------------------------------
                                | Sudah diperiksa admin dan perlu diperbaiki
                                |--------------------------------------------------------------------------
                                */

                                $document->review_status === ParticipantApplicationDocument::REVIEW_REVISION =>
                                    'bg-red-50 text-red-700',


                                /*
                                |--------------------------------------------------------------------------
                                | Lolos pemeriksaan otomatis dan belum diperiksa admin
                                |--------------------------------------------------------------------------
                                */

                                default =>
                                    'bg-blue-50 text-blue-700',
                            };


                            $reviewLabel = match(true) {

                                /*
                                |--------------------------------------------------------------------------
                                | Pemeriksaan otomatis gagal
                                |--------------------------------------------------------------------------
                                */

                                $automatedStatus !== 'passed' =>
                                    'Menunggu perbaikan peserta',


                                /*
                                |--------------------------------------------------------------------------
                                | Sudah disetujui admin
                                |--------------------------------------------------------------------------
                                */

                                $document->review_status === ParticipantApplicationDocument::REVIEW_APPROVED =>
                                    'Disetujui admin',


                                /*
                                |--------------------------------------------------------------------------
                                | Admin meminta perbaikan
                                |--------------------------------------------------------------------------
                                */

                                $document->review_status === ParticipantApplicationDocument::REVIEW_REVISION =>
                                    'Menunggu perbaikan peserta',


                                /*
                                |--------------------------------------------------------------------------
                                | Lolos otomatis dan siap diperiksa admin
                                |--------------------------------------------------------------------------
                                */

                                default =>
                                    'Siap diperiksa admin',
                            };


                            /*
                            |--------------------------------------------------------------------------
                            | APAKAH SIAP DIPERIKSA ADMIN? (untuk kolom Aksi)
                            |--------------------------------------------------------------------------
                            |
                            | Sengaja TIDAK mengecek review_status === REVIEW_SUBMITTED secara kaku,
                            | karena nilai review_status pada data lama/impor bisa saja null atau
                            | berbeda. Yang penting: sudah lolos otomatis DAN belum ada keputusan
                            | admin (belum approved, belum revision_required).
                            |
                            */

                            $awaitingAdminReview = $automatedStatus === 'passed'
                                && ! in_array($document->review_status, [
                                    ParticipantApplicationDocument::REVIEW_APPROVED,
                                    ParticipantApplicationDocument::REVIEW_REVISION,
                                ], true);

                        @endphp


                        <tr class="hover:bg-light/30">

                            {{-- Peserta --}}
                            <td class="px-5 py-5">

                                <p class="font-bold text-navy">
                                    {{ $document->application?->participant?->name ?? 'Nama tidak tersedia' }}
                                </p>

                                @if($document->application?->participant?->email)

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ $document->application->participant->email }}
                                    </p>

                                @endif

                            </td>


                            {{-- Dokumen --}}
                            <td class="px-5 py-5">

                                <p class="max-w-[220px] truncate font-semibold text-navy">
                                    {{ $document->original_name }}
                                </p>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Versi {{ $document->version }}
                                    ·
                                    {{ $document->created_at->format('d M Y, H:i') }}
                                </p>

                            </td>


                            {{-- Pemeriksaan Otomatis --}}
                            <td class="px-5 py-5">

                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $automatedClass }}"
                                >
                                    {{ $automatedLabel }}
                                </span>

                            </td>


                            {{-- Status Proses --}}
                            <td class="px-5 py-5">

                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $reviewClass }}"
                                >
                                    {{ $reviewLabel }}
                                </span>

                            </td>


                            {{-- Aksi --}}
                            <td class="px-5 py-5">

                                {{--
                                |--------------------------------------------------------------------------
                                | 1. Lolos otomatis + belum ada keputusan admin
                                |    → Admin perlu melakukan pemeriksaan manual
                                |--------------------------------------------------------------------------
                                --}}

                                @if($awaitingAdminReview)

                                    <a
                                        href="{{ route('admin.pemeriksaan-dokumen.show', $document) }}"
                                        class="inline-flex items-center rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white"
                                    >
                                        Periksa
                                    </a>


                                {{--
                                |--------------------------------------------------------------------------
                                | 2. Sudah disetujui admin
                                |    → Tidak perlu diperiksa lagi, hanya dilihat
                                |--------------------------------------------------------------------------
                                --}}

                                @elseif(
                                    $document->review_status === ParticipantApplicationDocument::REVIEW_APPROVED
                                )

                                    <a
                                        href="{{ route('admin.pemeriksaan-dokumen.show', $document) }}"
                                        class="inline-flex items-center rounded-xl border border-border px-4 py-2.5 text-xs font-bold text-navy"
                                    >
                                        Lihat
                                    </a>


                                {{--
                                |--------------------------------------------------------------------------
                                | 3. Admin meminta perbaikan
                                |    → Peserta harus memperbaiki terlebih dahulu
                                |--------------------------------------------------------------------------
                                --}}

                                @elseif(
                                    $document->review_status === ParticipantApplicationDocument::REVIEW_REVISION
                                )

                                    <span class="text-xs font-semibold text-muted-foreground">
                                        --
                                    </span>


                                {{--
                                |--------------------------------------------------------------------------
                                | 4. Pemeriksaan otomatis belum lolos
                                |    → Bukan pekerjaan admin
                                |--------------------------------------------------------------------------
                                --}}

                                @else

                                    <span class="text-xs font-semibold text-muted-foreground">
                                        --
                                    </span>

                                @endif

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="5" class="px-5 py-12 text-center">

                                <p class="font-bold text-navy">
                                    Belum ada surat permohonan
                                </p>

                                <p class="mt-1 text-sm text-muted-foreground">
                                    Surat yang diunggah peserta akan muncul di halaman ini.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if($documents->hasPages())

            <div class="border-t border-border p-5">
                {{ $documents->links() }}
            </div>

        @endif

    </div>

</div>

@endsection