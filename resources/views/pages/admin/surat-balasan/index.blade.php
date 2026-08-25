@extends('layouts.admin')

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
            Kelola surat balasan untuk peserta yang telah mengirim bukti pengisian Google Form.
        </p>
    </div>


    {{-- Success --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif


    {{-- Table --}}
    <div class="overflow-hidden rounded-[2rem] border border-border bg-white shadow-sm">

        <div class="border-b border-border p-5 sm:p-6">
            <h2 class="text-lg font-extrabold text-navy">
                Daftar Pengajuan
            </h2>

            <p class="mt-1 text-sm text-muted-foreground">
                Peserta yang telah mengirim bukti pengisian Google Form.
            </p>
        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-light/60 text-left">
                    <tr>
                        <th class="px-5 py-4 font-bold text-navy">
                            No
                        </th>

                        <th class="px-5 py-4 font-bold text-navy">
                            Peserta
                        </th>

                        <th class="px-5 py-4 font-bold text-navy">
                            Waktu Upload
                        </th>

                        <th class="px-5 py-4 font-bold text-navy">
                            Bukti Pengisian
                        </th>

                        <th class="px-5 py-4 font-bold text-navy">
                            Surat Balasan
                        </th>

                        <th class="px-5 py-4 font-bold text-navy">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border">
                    @forelse($participants as $index => $participant)
                        @php
                            $replyLetter = $participant->replyLetter;
                            $application = $participant->applications
                                ->sortByDesc('created_at')
                                ->first();
                            $proof = $application?->latestDocument(
                                \App\Models\ParticipantApplicationDocument::TYPE_INTERNSHIP_FORM_PROOF
                            );
                        @endphp

                        <tr class="hover:bg-light/30">
                            {{-- No --}}
                            <td class="px-5 py-5 font-semibold text-muted-foreground">
                                {{ $participants->firstItem() + $index }}
                            </td>

                            {{-- Peserta --}}
                            <td class="px-5 py-5">
                                <p class="font-bold text-navy">
                                    {{ $participant->name ?? '-' }}
                                </p>

                                @if($participant->email)
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ $participant->email }}
                                    </p>
                                @endif
                            </td>

                            {{-- Waktu Upload --}}
                            <td class="px-5 py-5">
                                @if($proof)
                                    <p class="font-semibold text-navy">
                                        {{ $proof->created_at->format('d M Y') }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ $proof->created_at->format('H:i') }} WIB
                                    </p>
                                @else
                                    <span class="text-muted-foreground">
                                        -
                                    </span>
                                @endif
                            </td>

                            {{-- Bukti Pengisian --}}
                            <td class="px-5 py-5">
                                @if($proof)
                                    <a
                                        href="{{ route('admin.surat-balasan.proof.preview', $proof) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-2 rounded-xl border border-ocean/20 bg-ocean/5 px-3 py-2 text-xs font-bold text-ocean hover:bg-ocean/10"
                                    >
                                        <i data-lucide="eye" class="h-4 w-4"></i>
                                        Lihat Bukti
                                    </a>
                                @else
                                    <span class="text-xs text-muted-foreground">
                                        Belum ada bukti
                                    </span>
                                @endif
                            </td>

                            {{-- Surat Balasan --}}
                            <td class="px-5 py-5">
                                @if($replyLetter)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                        Sudah dikirim
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                        Belum dikirim
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-5">
                                @if($application)
                                    <div class="flex flex-wrap gap-2">
                                        {{-- Upload Surat --}}
                                        <button
                                            type="button"
                                            @if($replyLetter)
                                                disabled
                                                title="Surat balasan sudah dikirim"
                                            @else
                                                onclick="document.getElementById('upload-{{ $application->id }}').click()"
                                            @endif
                                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold
                                                {{ $replyLetter
                                                    ? 'cursor-not-allowed bg-navy/40 text-white/70 opacity-60'
                                                    : 'bg-navy text-white hover:opacity-90'
                                                }}"
                                        >
                                            <i data-lucide="upload" class="h-4 w-4"></i>
                                            {{ $replyLetter ? 'Sudah Diunggah' : 'Upload Surat' }}
                                        </button>

                                        {{-- Lihat Surat --}}
                                        @if($replyLetter)
                                            <a
                                                href="{{ route('admin.surat-balasan.download', $replyLetter) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-2 rounded-xl border border-border px-4 py-2.5 text-xs font-bold text-navy hover:bg-light"
                                            >
                                                <i data-lucide="eye" class="h-4 w-4"></i>
                                                Lihat Surat
                                            </a>
                                        @endif
                                    </div>

                                    {{-- Hidden Upload Form --}}
                                    @unless($replyLetter)
                                        <form
                                            id="form-{{ $application->id }}"
                                            method="POST"
                                            action="{{ route('admin.surat-balasan.upload', $participant) }}"
                                            enctype="multipart/form-data"
                                            class="hidden"
                                        >
                                            @csrf
                                            <input
                                                id="upload-{{ $application->id }}"
                                                type="file"
                                                name="reply_letter"
                                                accept="application/pdf"
                                                onchange="document.getElementById('form-{{ $application->id }}').submit()"
                                            >
                                        </form>
                                    @endunless
                                @else
                                    <span class="text-xs text-muted-foreground">
                                        Tidak ada aplikasi
                                    </span>
                                @endif
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-bold text-navy">
                                    Belum ada pengajuan
                                </p>

                                <p class="mt-1 text-sm text-muted-foreground">
                                    Peserta yang telah mengunggah bukti Google Form akan muncul di sini.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        @if($participants->hasPages())
            <div class="border-t border-border p-5">
                {{ $participants->links() }}
            </div>
        @endif

    </div>

</div>

@endsection