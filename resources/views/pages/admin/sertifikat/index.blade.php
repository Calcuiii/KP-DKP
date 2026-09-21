@extends('layouts.admin')

@section('title', 'Unggah Sertifikat - SI-MELAYUR')

@section('content')
<div class="space-y-6">

    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Portal Peserta</p>
        <h1 class="mt-1 text-2xl font-extrabold text-navy">Unggah Sertifikat</h1>
        <p class="mt-2 text-sm text-muted-foreground">
            Verifikasi bukti presentasi dan pengisian form, cek data pada spreadsheet, lalu kirim sertifikat kepada peserta.
        </p>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @error('spreadsheet_verified')
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
            {{ $message }}
        </div>
    @enderror

    @error('certificate')
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
            {{ $message }}
        </div>
    @enderror

    <div class="overflow-hidden rounded-[2rem] border border-border bg-white shadow-sm">

        <div class="border-b border-border p-5 sm:p-6">
            <h2 class="text-lg font-extrabold text-navy">
                Peserta Siap Menerima Sertifikat
            </h2>

            <p class="mt-1 text-sm text-muted-foreground">
                Daftar peserta yang telah mengunggah bukti presentasi dan bukti pengisian Google Form.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-light/60 text-left">
                    <tr>
                        <th class="px-5 py-4 font-bold text-navy">Peserta</th>
                        <th class="px-5 py-4 font-bold text-navy">Bukti Presentasi</th>
                        <th class="px-5 py-4 font-bold text-navy">Bukti Google Form</th>
                        <th class="px-5 py-4 font-bold text-navy">Status Sertifikat</th>
                        <th class="px-5 py-4 font-bold text-navy">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border">
                    @forelse ($applications as $application)
                        <tr class="hover:bg-light/30">

                            {{-- Peserta --}}
                            <td class="px-5 py-5">
                                <p class="font-bold text-navy">
                                    {{ $application->participant->name }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ $application->participant->email }}
                                </p>
                            </td>

                            {{-- Bukti Presentasi --}}
                            <td class="px-5 py-5">
                                <button
                                    type="button"
                                    data-open-modal="preview-presentasi-{{ $application->id }}"
                                    class="text-xs font-bold text-ocean underline"
                                >
                                    Lihat Foto
                                </button>
                                <p class="mt-1 text-[11px] text-muted-foreground">
                                    {{ $application->presentation_date?->format('d M Y') ?? '-' }}
                                </p>
                            </td>

                            {{-- Bukti Google Form --}}
                            <td class="px-5 py-5">
                                <button
                                    type="button"
                                    data-open-modal="preview-gform-{{ $application->id }}"
                                    class="text-xs font-bold text-ocean underline"
                                >
                                    Lihat Bukti
                                </button>
                                <p class="mt-1 text-[11px] text-muted-foreground">
                                    {{ $application->completion_form_submitted_at?->format('d M Y, H:i') ?? '-' }}
                                </p>
                            </td>

                            {{-- Status Sertifikat --}}
                            <td class="px-5 py-5">
                                @if ($application->certificate_published_at)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                        Terkirim {{ $application->certificate_published_at->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                        Menunggu verifikasi
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-5">
                                <button
                                    type="button"
                                    data-open-modal="cert-modal-{{ $application->id }}"
                                    class="inline-flex items-center rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white"
                                >
                                    {{ $application->certificate_published_at ? 'Kirim Ulang' : 'Kirim Sertifikat' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p class="font-bold text-navy">
                                    Belum ada peserta yang siap menerima sertifikat
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Peserta akan muncul di sini setelah mengunggah bukti presentasi dan pengisian form.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($applications->hasPages())
            <div class="border-t border-border p-5">
                {{ $applications->links() }}
            </div>
        @endif

    </div>
</div>

{{-- Modal-modal per peserta: preview presentasi, preview bukti gform, dan kirim sertifikat --}}
@foreach ($applications as $application)

    {{-- Modal: Preview Bukti Presentasi --}}
    <div
        data-modal="preview-presentasi-{{ $application->id }}"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
    >
        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-navy">
                    Bukti Presentasi — {{ $application->participant->name }}
                </h3>
                <button
                    type="button"
                    data-close-modal="preview-presentasi-{{ $application->id }}"
                    class="rounded-xl p-2 hover:bg-accent"
                >
                    <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
                </button>
            </div>

            <div class="max-h-[70vh] overflow-auto rounded-xl border border-border bg-light/40 p-2">
                <img
                    src="{{ route('admin.sertifikat.presentasi', $application) }}"
                    alt="Bukti Presentasi"
                    class="mx-auto max-w-full rounded-lg"
                    loading="lazy"
                >
            </div>
        </div>
    </div>

    {{-- Modal: Preview Bukti Google Form --}}
    <div
        data-modal="preview-gform-{{ $application->id }}"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
    >
        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-navy">
                    Bukti Google Form — {{ $application->participant->name }}
                </h3>
                <button
                    type="button"
                    data-close-modal="preview-gform-{{ $application->id }}"
                    class="rounded-xl p-2 hover:bg-accent"
                >
                    <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
                </button>
            </div>

            <div class="max-h-[70vh] overflow-auto rounded-xl border border-border bg-light/40 p-2">
                <img
                    src="{{ route('admin.sertifikat.bukti-gform', $application) }}"
                    alt="Bukti Google Form"
                    class="mx-auto max-w-full rounded-lg"
                    loading="lazy"
                >
            </div>
        </div>
    </div>

    {{-- Modal: Kirim Sertifikat --}}
    <div
        data-modal="cert-modal-{{ $application->id }}"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
    >
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">

            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-navy">
                    Kirim Sertifikat — {{ $application->participant->name }}
                </h3>

                <button
                    type="button"
                    data-close-modal="cert-modal-{{ $application->id }}"
                    class="rounded-xl p-2 hover:bg-accent"
                >
                    <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
                </button>
            </div>

            <form
                method="POST"
                action="{{ route('admin.sertifikat.kirim', $application) }}"
                enctype="multipart/form-data"
                class="space-y-4"
            >
                @csrf

                <label class="flex items-start gap-2 text-xs font-medium text-navy">
                    <input type="checkbox" name="spreadsheet_verified" value="1" class="mt-0.5">
                    <span>
                        Saya sudah memeriksa dan mencocokkan data peserta ini pada spreadsheet Dinas.
                    </span>
                </label>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold">
                        File Sertifikat (PDF)
                    </label>
                    <input
                        type="file"
                        name="certificate"
                        accept="application/pdf"
                        class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-ocean py-3 text-sm font-semibold text-white hover:opacity-90"
                >
                    Kirim ke Peserta
                </button>
            </form>
        </div>
    </div>
@endforeach

@endsection