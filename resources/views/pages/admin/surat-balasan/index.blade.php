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

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-extrabold">Keputusan belum dapat dikirim:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
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
                            Keputusan &amp; Periode
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

                            {{-- Keputusan dan periode --}}
                            <td class="px-5 py-5">
                                @if($application?->decision === 'accepted')
                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Diterima</span>
                                    <p class="mt-2 whitespace-nowrap text-xs font-semibold text-navy">
                                        {{ $application->official_started_at?->translatedFormat('d M Y') }} – {{ $application->official_ended_at?->translatedFormat('d M Y') }}
                                    </p>
                                @elseif($application?->decision === 'rejected')
                                    <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">Ditolak</span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Belum diputuskan</span>
                                @endif
                                <p class="mt-2 text-[11px] text-muted-foreground">{{ $replyLetter ? 'Surat sudah dikirim' : 'Surat belum dikirim' }}</p>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-5">
                                @if($application)
                                    <div class="flex flex-wrap items-center gap-2">
                                        <details class="group">
                                            <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white hover:opacity-90">
                                                <i data-lucide="send" class="h-4 w-4"></i>
                                                {{ $replyLetter ? 'Perbarui Keputusan' : 'Tetapkan Keputusan' }}
                                            </summary>
                                            <form method="POST" action="{{ route('admin.surat-balasan.upload', $participant) }}" enctype="multipart/form-data" class="mt-3 w-[19rem] space-y-3 rounded-2xl border border-border bg-white p-4 shadow-xl">
                                                @csrf
                                                <div>
                                                    <label for="decision-{{ $application->id }}" class="text-xs font-extrabold text-navy">Keputusan resmi</label>
                                                    <select id="decision-{{ $application->id }}" name="decision" required class="mt-1 block w-full rounded-xl border border-border bg-white px-3 py-2.5 text-xs">
                                                        <option value="">Pilih keputusan</option>
                                                        <option value="accepted" @selected($application->decision === 'accepted')>Diterima</option>
                                                        <option value="rejected" @selected($application->decision === 'rejected')>Ditolak</option>
                                                    </select>
                                                </div>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div><label class="text-[11px] font-bold text-navy">Mulai <span class="text-red-600">*</span></label><input type="date" name="official_started_at" value="{{ $application->official_started_at?->format('Y-m-d') }}" class="mt-1 block w-full rounded-xl border border-border px-2 py-2 text-xs"></div>
                                                    <div><label class="text-[11px] font-bold text-navy">Selesai <span class="text-red-600">*</span></label><input type="date" name="official_ended_at" value="{{ $application->official_ended_at?->format('Y-m-d') }}" class="mt-1 block w-full rounded-xl border border-border px-2 py-2 text-xs"></div>
                                                </div>
                                                <p class="text-[10px] leading-relaxed text-muted-foreground">Tanggal wajib diisi jika diterima dan akan langsung muncul pada kalender peserta.</p>
                                                <div><label class="text-xs font-extrabold text-navy">Surat balasan PDF</label><input type="file" name="reply_letter" accept="application/pdf,.pdf" required class="mt-1 block w-full rounded-xl border border-border p-2 text-xs"></div>
                                                <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-teal px-4 py-2.5 text-xs font-extrabold text-white"><i data-lucide="send" class="h-4 w-4"></i>Kirim Keputusan &amp; Surat</button>
                                            </form>
                                        </details>

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
