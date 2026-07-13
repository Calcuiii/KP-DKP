@extends('layouts.app')

@section('title', 'DKP Assistant | Chatbot')

@section(
    'meta_description',
    'Tanyakan informasi Kerja Praktik dan Magang melalui DKP Assistant berdasarkan dokumen resmi.'
)

@section('content')
    <div class="flex min-h-screen bg-light font-sans text-navy">

        @include('components.chatbot.sidebar')

        <main class="flex min-h-screen min-w-0 flex-1 flex-col">

            <header class="flex h-16 items-center border-b border-border bg-white px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean text-white">
                        <i data-lucide="fish" class="h-5 w-5"></i>
                    </div>

                    <div>
                        <h1 class="text-sm font-semibold text-navy">
                            DKP Assistant
                        </h1>

                        <p class="flex items-center gap-1.5 text-[10px] text-muted-foreground">
                            <span class="h-1.5 w-1.5 rounded-full bg-teal"></span>
                            Online
                        </p>
                    </div>
                </div>

                
                    href="{{ route('landing') }}"
                    class="ml-auto text-xs text-muted-foreground transition hover:text-ocean"
                >
                    Beranda
                </a>
            </header>

            {{--
                CATATAN: empty state, contoh percakapan, dan loading state
                sengaja ditampilkan semua dulu di bawah ini supaya bisa
                dicek visualnya. Nanti kalau JS sudah dibuat, hanya salah
                satu yang akan ditampilkan tergantung status chat.
            --}}

            <section class="flex flex-1 flex-col items-center justify-center overflow-y-auto px-4 py-10 text-center">
                <div class="w-full max-w-xl">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-ocean text-white shadow-md">
                        <i data-lucide="fish" class="h-8 w-8"></i>
                    </div>

                    <h2 class="mt-5 text-xl font-bold text-navy sm:text-2xl">
                        Halo, ada yang bisa DKP Assistant bantu?
                    </h2>

                    <p class="mt-2 text-sm text-muted-foreground">
                        Tanyakan informasi seputar Kerja Praktik dan Magang.
                    </p>

                    <div class="mt-6 grid grid-cols-1 gap-2 text-left sm:grid-cols-2">
                        <button
                            type="button"
                            class="rounded-xl border border-border bg-white px-4 py-3 text-sm text-navy shadow-sm transition hover:border-ocean hover:shadow-md"
                        >
                            Apa saja persyaratan pengajuan magang?
                        </button>

                        <button
                            type="button"
                            class="rounded-xl border border-border bg-white px-4 py-3 text-sm text-navy shadow-sm transition hover:border-ocean hover:shadow-md"
                        >
                            Bagaimana alur pengajuan KP?
                        </button>

                        <button
                            type="button"
                            class="rounded-xl border border-border bg-white px-4 py-3 text-sm text-navy shadow-sm transition hover:border-ocean hover:shadow-md"
                        >
                            Berapa lama proses pengajuan?
                        </button>

                        <button
                            type="button"
                            class="rounded-xl border border-border bg-white px-4 py-3 text-sm text-navy shadow-sm transition hover:border-ocean hover:shadow-md"
                        >
                            Dokumen apa saja yang harus disiapkan?
                        </button>
                    </div>
                </div>
            </section>

            <section class="flex-1 overflow-y-auto py-4">
                @include('components.chatbot.message')

                @include('components.chatbot.loading')
            </section>

            <div class="border-t border-border bg-white p-4 sm:p-6">
                <div class="mx-auto max-w-3xl">
                    <div class="flex items-end gap-2 rounded-2xl border border-border bg-input-background px-4 py-3">
                        <textarea
                            rows="1"
                            placeholder="Tanyakan informasi tentang KP dan Magang..."
                            maxlength="500"
                            class="max-h-32 min-h-[20px] flex-1 resize-none bg-transparent text-sm text-navy placeholder:text-muted-foreground focus:outline-none"
                        ></textarea>

                        <div class="flex shrink-0 items-center gap-2">
                            <span class="text-[10px] text-muted-foreground">0/500</span>

                            <button
                                type="button"
                                disabled
                                class="flex h-8 w-8 items-center justify-center rounded-xl bg-ocean text-white transition hover:bg-navy disabled:opacity-40"
                            >
                                <i data-lucide="send" class="h-3.5 w-3.5"></i>
                            </button>
                        </div>
                    </div>

                    <p class="mt-2 text-center text-[10px] text-muted-foreground">
                        DKP Assistant dapat menghasilkan jawaban yang kurang tepat. Pastikan kembali informasi penting melalui layanan resmi.
                    </p>
                </div>
            </div>

        </main>

    </div>
@endsection