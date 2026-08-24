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
            Kelola surat balasan untuk peserta yang telah menyelesaikan proses pengajuan.
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
                Daftar Peserta
            </h2>

            <p class="mt-1 text-sm text-muted-foreground">
                Upload surat balasan kepada peserta.
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
                        @endphp

                        <tr class="hover:bg-light/30">

                            {{-- No --}}
                            <td class="px-5 py-5 font-semibold text-muted-foreground">

                                {{ $participants->firstItem() + $index }}

                            </td>


                            {{-- Peserta --}}
                            <td class="px-5 py-5">

                                <p class="font-bold text-navy">
                                    {{ $participant->name }}
                                </p>

                                @if($participant->email)

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ $participant->email }}
                                    </p>

                                @endif

                            </td>


                            {{-- Status --}}
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

                                <div class="flex flex-wrap gap-2">

                                    {{-- Upload / Ganti --}}
                                    <button
                                        type="button"
                                        onclick="document.getElementById('upload-{{ $participant->id }}').click()"
                                        class="inline-flex items-center rounded-xl bg-navy px-4 py-2.5 text-xs font-bold text-white hover:opacity-90"
                                    >
                                        {{ $replyLetter ? 'Ganti Surat' : 'Upload Surat' }}
                                    </button>


                                    {{-- Lihat --}}
                                    @if($replyLetter)

                                        <a
                                            href="{{ route('admin.surat-balasan.download', $replyLetter) }}"
                                            class="inline-flex items-center rounded-xl border border-border px-4 py-2.5 text-xs font-bold text-navy hover:bg-light"
                                        >
                                            Lihat Surat
                                        </a>

                                    @endif

                                </div>


                                {{-- Hidden upload form --}}
                                <form
                                    id="form-{{ $participant->id }}"
                                    method="POST"
                                    action="{{ route('admin.surat-balasan.upload', $participant) }}"
                                    enctype="multipart/form-data"
                                    class="hidden"
                                >

                                    @csrf

                                    <input
                                        id="upload-{{ $participant->id }}"
                                        type="file"
                                        name="reply_letter"
                                        accept="application/pdf"
                                        onchange="document.getElementById('form-{{ $participant->id }}').submit()"
                                    >

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="px-5 py-12 text-center"
                            >

                                <p class="font-bold text-navy">
                                    Belum ada peserta
                                </p>

                                <p class="mt-1 text-sm text-muted-foreground">
                                    Peserta yang telah masuk ke proses pengajuan akan muncul di sini.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if($participants->hasPages())

            <div class="border-t border-border p-5">
                {{ $participants->links() }}
            </div>

        @endif

    </div>

</div>

@endsection