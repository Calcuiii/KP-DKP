@extends('layouts.admin')

@php
    use App\Models\ParticipantApplicationDocument;
@endphp

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">
            Tindak Lanjut Manual
        </p>

        <h1 class="mt-1 text-2xl font-extrabold text-navy">
            Tindak Lanjut WOPPS
        </h1>

        <p class="mt-2 text-sm text-muted-foreground">
            Peserta layanan WOPPS (wawancara, observasi, penelitian, pendataan, survei) yang sudah menyelesaikan seluruh proses pengisian. Periksa kelengkapan dokumen dan spreadsheet, lalu hubungi peserta melalui WhatsApp.
        </p>
    </div>


    {{-- Flash message --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif


    {{-- Tabel --}}
    <div class="overflow-hidden rounded-[2rem] border border-border bg-white shadow-sm">

        <div class="border-b border-border p-5 sm:p-6">
            <h2 class="text-lg font-extrabold text-navy">
                Daftar Peserta WOPPS
            </h2>

            <p class="mt-1 text-sm text-muted-foreground">
                Diurutkan dari yang paling baru mengisi Form WOPPS.
            </p>
        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-light/60 text-left">
                    <tr>
                        <th class="px-5 py-4 font-bold text-navy">Nama &amp; Email</th>
                        <th class="px-5 py-4 font-bold text-navy">Surat Permohonan</th>
                        <th class="px-5 py-4 font-bold text-navy">Ethics Approval</th>
                        <th class="px-5 py-4 font-bold text-navy">Form WOPPS</th>
                        <th class="px-5 py-4 font-bold text-navy">Status Tindak Lanjut</th>
                        <th class="px-5 py-4 font-bold text-navy">Aksi</th>
                    </tr>
                </thead>


                <tbody class="divide-y divide-border">

                    @forelse($applications as $application)

                        @php
                            $participant = $application->participant;

                            $requestLetter = $application->documents
                                ->where('type', ParticipantApplicationDocument::TYPE_REQUEST_LETTER)
                                ->sortByDesc('version')
                                ->first();

                            $ethicsApproval = $application->documents
                                ->where('type', ParticipantApplicationDocument::TYPE_ETHICS_APPROVAL)
                                ->sortByDesc('version')
                                ->first();

                            $woppsFormProof = $application->documents
                                ->where('type', ParticipantApplicationDocument::TYPE_WOPPS_FORM_PROOF)
                                ->first();

                            $isContacted = $application->pic_contacted_at !== null;
                        @endphp

                        <tr class="hover:bg-light/30">

                            {{-- Peserta --}}
                            <td class="px-5 py-5">
                                <p class="font-bold text-navy">{{ $participant?->name ?? 'Nama tidak tersedia' }}</p>
                                <p class="mt-1 text-xs text-muted-foreground">{{ $participant?->email ?? '-' }}</p>
                            </td>

                            {{-- Surat Permohonan --}}
                            <td class="px-5 py-5">
                                @if($requestLetter?->review_status === ParticipantApplicationDocument::REVIEW_APPROVED)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                        <i data-lucide="check-circle" class="h-3.5 w-3.5"></i> Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                        <i data-lucide="clock" class="h-3.5 w-3.5"></i> Belum
                                    </span>
                                @endif
                            </td>

                            {{-- Ethics Approval --}}
                            <td class="px-5 py-5">
                                @if($ethicsApproval?->review_status === ParticipantApplicationDocument::REVIEW_APPROVED)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                        <i data-lucide="check-circle" class="h-3.5 w-3.5"></i> Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                        <i data-lucide="clock" class="h-3.5 w-3.5"></i> Belum
                                    </span>
                                @endif
                            </td>

                            {{-- Form WOPPS --}}
                            <td class="px-5 py-5">
                                @if($woppsFormProof)
                                    <a
                                        href="{{ route('admin.wopps-follow-up.download', $woppsFormProof) }}"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 hover:bg-emerald-100"
                                    >
                                        <i data-lucide="file-check" class="h-3.5 w-3.5"></i> Lihat Bukti
                                    </a>
                                    <p class="mt-1 text-[11px] text-muted-foreground">
                                        {{ $application->google_form_confirmed_at?->format('d M Y, H:i') }}
                                    </p>
                                @else
                                    <span class="text-xs font-semibold text-muted-foreground">--</span>
                                @endif
                            </td>

                            {{-- Status Tindak Lanjut --}}
                            <td class="px-5 py-5">
                                @if($isContacted)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal/10 px-3 py-1 text-xs font-bold text-teal">
                                        <i data-lucide="check-circle" class="h-3.5 w-3.5"></i> Sudah Dihubungi
                                    </span>
                                    <p class="mt-1 text-[11px] text-muted-foreground">
                                        {{ $application->pic_contacted_at->diffForHumans() }}
                                    </p>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">
                                        <i data-lucide="alert-circle" class="h-3.5 w-3.5"></i> Belum Dihubungi
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-5">
                                <form method="POST" action="{{ route('admin.wopps-follow-up.mark-contacted', $application) }}">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-xs font-bold {{ $isContacted ? 'border border-border text-navy' : 'bg-navy text-white' }}"
                                    >
                                        @if($isContacted)
                                            <i data-lucide="rotate-ccw" class="h-3.5 w-3.5"></i> Batalkan
                                        @else
                                            <i data-lucide="phone-call" class="h-3.5 w-3.5"></i> Tandai Sudah Dihubungi
                                        @endif
                                    </button>
                                </form>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-bold text-navy">Belum ada pengajuan WOPPS</p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Peserta yang sudah mengisi Form WOPPS akan muncul di halaman ini.
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

@endsection