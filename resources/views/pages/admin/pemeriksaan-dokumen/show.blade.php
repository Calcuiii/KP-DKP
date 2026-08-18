@extends('layouts.admin')

@php
    use App\Models\ParticipantApplicationDocument;
@endphp

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div>
        <a
            href="{{ route('admin.pemeriksaan-dokumen') }}"
            class="text-sm font-bold text-ocean"
        >
            ← Kembali ke pemeriksaan dokumen
        </a>

        <p class="mt-5 text-xs font-bold uppercase tracking-[0.18em] text-teal">
            Pemeriksaan Surat
        </p>

        <h1 class="mt-2 text-2xl font-extrabold text-navy">
            Verifikasi Surat Permohonan
        </h1>
    </div>


    {{-- Success --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif


    <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">


        {{-- =========================
             INFORMASI DOKUMEN
        ========================== --}}
        <section class="space-y-6">

            <article class="rounded-[2rem] border border-border bg-white p-6 shadow-sm">

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-ocean">
                            Dokumen peserta
                        </p>

                        <h2 class="mt-2 text-xl font-extrabold text-navy">
                            {{ $document->original_name }}
                        </h2>

                        <p class="mt-2 text-xs text-muted-foreground">
                            Versi {{ $document->version }}
                            ·
                            Diupload {{ $document->created_at->format('d M Y, H:i') }}
                        </p>

                    </div>

                    <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">
                        PDF
                    </span>

                </div>


                {{-- Download --}}
                <div class="mt-6">

                    <a
                        href="{{ route('admin.pemeriksaan-dokumen.download', $document) }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white"
                    >
                        <i data-lucide="file-text" class="h-4 w-4"></i>
                        Buka
                    </a>

                </div>

            </article>


            {{-- =========================
                 DATA PESERTA
            ========================== --}}
            <article class="rounded-[2rem] border border-border bg-white p-6 shadow-sm">

                <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">
                    Informasi Peserta
                </p>

                <h2 class="mt-2 text-xl font-extrabold text-navy">
                    Data Pengajuan
                </h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">

                    <div class="rounded-2xl bg-light/60 p-4">
                        <p class="text-xs text-muted-foreground">
                            Nama
                        </p>

                        <p class="mt-1 font-bold text-navy">
                            {{ $document->application?->participant?->name ?? '-' }}
                        </p>
                    </div>


                    <div class="rounded-2xl bg-light/60 p-4">
                        <p class="text-xs text-muted-foreground">
                            Email
                        </p>

                        <p class="mt-1 font-bold text-navy">
                            {{ $document->application?->participant?->email ?? '-' }}
                        </p>
                    </div>

                </div>

            </article>


            {{-- =========================
                 HASIL OTOMATIS
            ========================== --}}
            @if($document->automated_check_results)

                @php
                    $result = $document->automated_check_results;
                @endphp

                <article class="rounded-[2rem] border border-border bg-white p-6 shadow-sm">

                    <div class="flex items-start gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-ocean/10 text-ocean">
                            <i data-lucide="scan-search" class="h-5 w-5"></i>
                        </span>

                        <div>

                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-ocean">
                                Pemeriksaan Otomatis
                            </p>

                            <h2 class="mt-1 text-xl font-extrabold text-navy">
                                Hasil Analisis Sistem
                            </h2>

                        </div>

                    </div>


                    @if(!empty($result['summary']))

                        <div class="mt-5 rounded-2xl bg-light/60 p-4 text-sm leading-relaxed text-muted-foreground">
                            {{ $result['summary'] }}
                        </div>

                    @endif


                    @if(!empty($result['checks']))

                        <div class="mt-5 space-y-3">

                            @foreach($result['checks'] as $check)

                                <div class="flex items-start gap-3 rounded-2xl border border-border p-4">

                                    <span
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold
                                        {{
                                            $check['status'] === 'passed'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : ($check['status'] === 'manual'
                                                    ? 'bg-blue-100 text-blue-700'
                                                    : 'bg-red-100 text-red-700')
                                        }}"
                                    >
                                        {{
                                            $check['status'] === 'passed'
                                                ? '✓'
                                                : ($check['status'] === 'manual' ? 'i' : '!')
                                        }}
                                    </span>

                                    <div>

                                        <p class="text-sm font-bold text-navy">
                                            {{ $check['label'] }}
                                        </p>

                                        <p class="mt-1 text-xs leading-relaxed text-muted-foreground">
                                            {{ $check['message'] }}
                                        </p>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endif


                    <div class="mt-5 rounded-xl bg-amber-50 p-4 text-xs leading-relaxed text-amber-800">

                        <strong>Catatan:</strong>
                        hasil pemeriksaan otomatis hanya menjadi pemeriksaan awal.
                        Keputusan akhir tetap dilakukan oleh administrator.

                    </div>

                </article>

            @endif

        </section>



        {{-- =========================
             PANEL KEPUTUSAN ADMIN
        ========================== --}}
        <aside>

            <div class="sticky top-6 rounded-[2rem] border border-border bg-white p-6 shadow-sm">

                <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">
                    Keputusan Admin
                </p>

                <h2 class="mt-2 text-xl font-extrabold text-navy">
                    Tentukan hasil pemeriksaan
                </h2>

                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                    Periksa dokumen asli dan bandingkan dengan hasil pemeriksaan otomatis sebelum mengambil keputusan.
                </p>


                {{-- Status saat ini --}}
                <div class="mt-5 rounded-2xl bg-light/60 p-4">

                    <p class="text-xs text-muted-foreground">
                        Status saat ini
                    </p>

                    <p class="mt-1 font-bold text-navy">

                        @if($document->review_status === ParticipantApplicationDocument::REVIEW_APPROVED)
                            Disetujui
                        @elseif($document->review_status === ParticipantApplicationDocument::REVIEW_REVISION)
                            Perlu Perbaikan
                        @else
                            Menunggu Pemeriksaan
                        @endif

                    </p>

                </div>


                {{-- =========================
                     APPROVE
                ========================== --}}
                <form
                method="POST"
                action="{{ route('admin.pemeriksaan-dokumen.approve', $document) }}"
                class="mt-6"
            >

                @csrf
                @method('PATCH')

                <label class="text-sm font-bold text-navy">
                    Catatan Admin
                </label>

                    <textarea
                        name="review_notes"
                        rows="4"
                        class="mt-2 w-full rounded-xl border border-border bg-background p-3 text-sm outline-none focus:border-ocean"
                        placeholder="Tambahkan catatan jika diperlukan..."
                    >{{ old('review_notes', $document->review_status === 'approved' ? $document->review_notes : '') }}</textarea>

                    <button
                        type="submit"
                        class="mt-3 w-full rounded-xl bg-teal px-5 py-3 text-sm font-bold text-white"
                        onclick="return confirm('Apakah Anda yakin ingin menyetujui surat ini?')"
                    >
                        ✓ Setujui Surat
                    </button>

                </form>


                {{-- =========================
                     REVISION
                ========================== --}}
                <div class="my-6 border-t border-border"></div>

                <form
                    method="POST"
                    action="{{ route('admin.pemeriksaan-dokumen.revision', $document) }}"
                >

                    @csrf
                    @method('PATCH')

                    <label class="text-sm font-bold text-navy">
                        Alasan Perbaikan
                    </label>

                    <textarea
                        name="review_notes"
                        rows="5"
                        required
                        class="mt-2 w-full rounded-xl border border-border bg-background p-3 text-sm outline-none focus:border-ocean"
                        placeholder="Contoh: Lokasi tujuan belum tercantum dalam surat..."
                    >{{ old('review_notes') }}</textarea>

                    @error('review_notes')
                        <p class="mt-2 text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <button
                        type="submit"
                        class="mt-3 w-full rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white"
                        onclick="return confirm('Kirim permintaan perbaikan kepada peserta?')"
                    >
                        ↻ Minta Perbaikan
                    </button>

                </form>


                <div class="mt-5 rounded-xl bg-blue-50 p-4 text-xs leading-relaxed text-blue-700">

                    <strong>Petunjuk:</strong>

                    <ul class="mt-2 space-y-1">
                        <li>• Pastikan identitas peserta benar.</li>
                        <li>• Pastikan lokasi tujuan tercantum.</li>
                        <li>• Pastikan tanggal mulai dan selesai tercantum.</li>
                        <li>• Pastikan surat dapat dibaca.</li>
                        <li>• Pastikan tujuan surat sesuai ketentuan.</li>
                    </ul>

                </div>

            </div>

        </aside>

    </div>

</div>

@endsection