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
                Surat yang telah diunggah peserta dan tersedia untuk pemeriksaan.
            </p>
        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-light/60 text-left">
                    <tr>
                        <th class="px-5 py-4 font-bold text-navy">Peserta</th>
                        <th class="px-5 py-4 font-bold text-navy">Dokumen</th>
                        <th class="px-5 py-4 font-bold text-navy">Pemeriksaan Otomatis</th>
                        <th class="px-5 py-4 font-bold text-navy">Status Admin</th>
                        <th class="px-5 py-4 font-bold text-navy">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border">

                    @forelse($documents as $document)

                        @php
                            $automatedStatus = $document->automated_check_status;

                            $automatedClass = match($automatedStatus) {
                                'passed' => 'bg-emerald-50 text-emerald-700',
                                'needs_revision',
                                'unreadable' => 'bg-red-50 text-red-700',
                                default => 'bg-amber-50 text-amber-700',
                            };

                            $automatedLabel = match($automatedStatus) {
                                'passed' => 'Lolos pemeriksaan awal',
                                'needs_revision' => 'Perlu perbaikan',
                                'unreadable' => 'Tidak terbaca',
                                default => 'Menunggu pemeriksaan',
                            };

                            $reviewClass = match($document->review_status) {
                                ParticipantApplicationDocument::REVIEW_APPROVED =>
                                    'bg-emerald-50 text-emerald-700',

                                ParticipantApplicationDocument::REVIEW_REVISION =>
                                    'bg-red-50 text-red-700',

                                default =>
                                    'bg-amber-50 text-amber-700',
                            };

                            $reviewLabel = match($document->review_status) {
                                ParticipantApplicationDocument::REVIEW_APPROVED =>
                                    'Disetujui',

                                ParticipantApplicationDocument::REVIEW_REVISION =>
                                    'Perlu perbaikan',

                                default =>
                                    'Menunggu admin',
                            };
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


                            {{-- Automated --}}
                            <td class="px-5 py-5">

                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $automatedClass }}">
                                    {{ $automatedLabel }}
                                </span>

                            </td>


                            {{-- Admin --}}
                            <td class="px-5 py-5">

                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $reviewClass }}">
                                    {{ $reviewLabel }}
                                </span>

                            </td>


                            {{-- Aksi --}}
                            <td class="px-5 py-5">

                                <a
                                    href="{{ route('admin.pemeriksaan-dokumen.show', $document) }}"
                                    class="inline-flex items-center rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white"
                                >
                                    Periksa
                                </a>

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


        @if($documents->hasPages())

            <div class="border-t border-border p-5">
                {{ $documents->links() }}
            </div>

        @endif

    </div>

</div>

@endsection
