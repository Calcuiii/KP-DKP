@extends('layouts.admin')

@php
    use App\Models\ParticipantApplicationDocument;
@endphp

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">
            Administrasi
        </p>

        <h1 class="mt-1 text-2xl font-extrabold text-navy">
            Surat Balasan
        </h1>

        <p class="mt-2 text-sm text-muted-foreground">
            Kelola keputusan dan surat balasan berdasarkan jenis layanan peserta.
        </p>
    </div>

    <x-admin.reply-letter-tabs />


    {{-- Flash message --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><p class="font-extrabold">Data belum dapat disimpan:</p><ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif


    {{-- Tabel --}}
    <div class="overflow-hidden rounded-[2rem] border border-border bg-white shadow-sm">

        <div class="border-b border-border p-5 sm:p-6">
            <h2 class="text-lg font-extrabold text-navy">
                Pengajuan WOPPS
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
                        <th class="px-5 py-4 font-bold text-navy">Keputusan &amp; Surat</th>
                        <th class="px-5 py-4 font-bold text-navy">Status Layanan</th>
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
                            $isCompleted = $application->completed_at !== null;
                            $replyLetter = $application->replyLetter;
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

                            {{-- Keputusan --}}
                            <td class="px-5 py-5">
                                @if($application->decision === 'accepted')
                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Diterima</span>
                                @elseif($application->decision === 'rejected')
                                    <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">Ditolak</span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Belum diputuskan</span>
                                @endif
                                @if($replyLetter)<a href="{{ route('admin.surat-balasan.download', $replyLetter) }}" class="mt-2 block text-xs font-bold text-ocean">Lihat surat balasan</a>@endif
                            </td>

                            {{-- Status layanan --}}
                            <td class="px-5 py-5">
                                @if($isCompleted)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"><i data-lucide="circle-check" class="h-3.5 w-3.5"></i>Selesai</span>
                                    <p class="mt-1 text-[11px] text-muted-foreground">{{ $application->completed_at->format('d M Y, H:i') }}</p>
                                @elseif($application->decision === 'rejected')
                                    <span class="text-xs font-semibold text-red-700">Pengajuan ditutup</span>
                                @elseif($isContacted)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal/10 px-3 py-1 text-xs font-bold text-teal">
                                        <i data-lucide="check-circle" class="h-3.5 w-3.5"></i> Sudah Dihubungi
                                    </span>
                                    <p class="mt-1 text-[11px] text-muted-foreground">
                                        {{ $application->pic_contacted_at->diffForHumans() }}
                                    </p>
                                @else
                                    <span class="text-xs font-semibold text-muted-foreground">{{ $application->decision === 'accepted' ? 'Menunggu dihubungi Dinas' : 'Menunggu keputusan' }}</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-5">
                                <div class="flex min-w-64 flex-col gap-2">
                                    @if(!$application->decision)
                                        <form method="POST" action="{{ route('admin.wopps-follow-up.decision', $application) }}" enctype="multipart/form-data" class="space-y-3 rounded-xl border border-border p-3">
                                            @csrf
                                            <select name="decision" required class="block w-full rounded-lg border border-border px-3 py-2 text-xs"><option value="">Pilih keputusan</option><option value="accepted">Diterima</option><option value="rejected">Ditolak</option></select>
                                            <div class="rounded-xl border border-dashed border-ocean/30 bg-ocean/[0.03] p-3">
                                                <label for="wopps-reply-letter-{{ $application->id }}" class="flex items-center gap-1.5 text-xs font-extrabold text-navy">
                                                    <i data-lucide="file-up" class="h-3.5 w-3.5 text-ocean" aria-hidden="true"></i>
                                                    Surat balasan PDF
                                                </label>
                                                <input id="wopps-reply-letter-{{ $application->id }}" type="file" name="reply_letter" accept="application/pdf,.pdf" required class="mt-2 block w-full cursor-pointer rounded-lg border border-border bg-white p-1.5 text-xs text-muted-foreground file:mr-3 file:rounded-md file:border-0 file:bg-ocean/10 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-ocean hover:file:bg-ocean/15">
                                                <p class="mt-1.5 text-[10px] text-muted-foreground">Pilih surat resmi dalam format PDF, maksimal 10 MB.</p>
                                            </div>
                                            <button class="w-full rounded-lg bg-navy px-3 py-2 text-xs font-bold text-white">Kirim Keputusan &amp; Surat</button>
                                        </form>
                                    @elseif($application->decision === 'accepted' && !$isCompleted)
                                        <form method="POST" action="{{ route('admin.wopps-follow-up.mark-contacted', $application) }}">@csrf<button class="w-full rounded-xl px-4 py-2.5 text-xs font-bold {{ $isContacted ? 'border border-border text-navy' : 'bg-navy text-white' }}">{{ $isContacted ? 'Batalkan tanda dihubungi' : 'Tandai Sudah Dihubungi' }}</button></form>
                                        @if($isContacted)
                                            <form method="POST" action="{{ route('admin.wopps-follow-up.complete', $application) }}" onsubmit="return confirm('Tandai layanan WOPPS ini selesai?')">@csrf<button class="w-full rounded-xl bg-teal px-4 py-2.5 text-xs font-bold text-white">Layanan WOPPS Selesai</button></form>
                                        @endif
                                    @else
                                        <span class="text-xs font-semibold text-muted-foreground">Tidak ada tindakan lanjutan.</span>
                                    @endif
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
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
