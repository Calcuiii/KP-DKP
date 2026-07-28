@extends('layouts.app')

@section('title', 'Buku Tamu | DKP Assistant')

@section('meta_description', 'Check-in Buku Tamu sebelum menggunakan layanan DKP Assistant.')

@section('hide_dev_nav', 'true')

@section('content')
    <div class="min-h-screen bg-slate-50 font-sans">
        @include('components.landing.navbar')

        <main class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-3xl items-center px-4 py-12 sm:px-6">
            <section class="w-full overflow-hidden rounded-3xl border border-border bg-white shadow-xl shadow-navy/5">
                <div class="bg-gradient-to-br from-navy to-ocean px-7 py-9 text-white sm:px-10">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15">
                        <i
                            data-lucide="file-text"
                            class="h-6 w-6"
                            aria-hidden="true"
                        ></i>
                    </div>

                    <p class="mb-2 text-sm font-semibold text-blue-100">
                        Check-in Pengunjung
                    </p>

                    <h1 class="text-3xl font-bold leading-tight sm:text-4xl">
                        Sebelum menggunakan layanan, silakan isi Buku Tamu
                    </h1>
                </div>

                <div class="px-7 py-8 sm:px-10">
                    <p class="text-base leading-relaxed text-muted-foreground">
                        Buku Tamu membantu Dinas Kelautan dan Perikanan Provinsi Jawa Timur melakukan pendataan layanan Magang dan PKL. Pengisian cukup dilakukan satu kali pada perangkat ini.
                    </p>

                    <div class="mt-6 rounded-2xl border border-ocean/20 bg-ocean/[0.05] p-4 text-sm leading-relaxed text-foreground">
                        <span class="font-semibold text-ocean">Sudah pernah mengisi?</span>
                        Pilih tombol “Saya sudah pernah mengisi” agar Anda dapat langsung menggunakan layanan tanpa mengisi ulang.
                    </div>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ $guestbookUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-ocean px-5 py-3.5 font-semibold text-white transition-opacity hover:opacity-90"
                        >
                            <i
                                data-lucide="file-text"
                                class="h-5 w-5"
                                aria-hidden="true"
                            ></i>

                            Isi Buku Tamu
                        </a>

                        <form action="{{ route('guestbook.complete') }}" method="POST" class="flex-1">
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-border px-5 py-3.5 font-semibold text-navy transition-colors hover:bg-slate-50"
                            >
                                <i
                                    data-lucide="check-circle"
                                    class="h-5 w-5 text-teal"
                                    aria-hidden="true"
                                ></i>

                                Saya sudah pernah mengisi
                            </button>
                        </form>
                    </div>

                    <p class="mt-5 text-center text-xs leading-relaxed text-muted-foreground">
                        Setelah selesai mengisi Google Form, kembali ke halaman ini lalu pilih “Saya sudah pernah mengisi”.
                    </p>
                </div>
            </section>
        </main>
    </div>
@endsection
