<section
    data-chat-empty
    class="chatbot-pattern-surface flex min-h-0 flex-1 overflow-y-auto px-4 py-8 sm:px-6"
>
    <div class="chatbot-pattern-content m-auto w-full max-w-2xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-ocean text-white shadow-md">
            <i data-lucide="fish" class="h-8 w-8"></i>
        </div>

        <h1 class="mt-5 text-2xl font-bold tracking-tight text-navy sm:text-3xl">
            Halo, selamat datang di Si-Molek!
        </h1>

        <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-muted-foreground sm:text-base">
            Saya siap membantu berdasarkan dokumen resmi Dinas Kelautan dan Perikanan Provinsi Jawa Timur. Anda ingin menanyakan informasi tentang apa?
        </p>

        <div data-chat-topic-options class="mx-auto mt-8 grid max-w-2xl gap-3 text-left sm:grid-cols-2">
            <button
                type="button"
                data-chat-topic="magang"
                class="group flex min-h-28 items-center gap-4 rounded-2xl border border-ocean/20 bg-white px-5 py-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-ocean hover:shadow-md"
            >
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-ocean/10 text-ocean">
                    <i data-lucide="graduation-cap" class="h-5 w-5"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-base font-bold text-navy">Magang / PKL</span>
                    <span class="mt-1 block text-xs leading-5 text-muted-foreground">Pengajuan, persyaratan, pelaksanaan, laporan, dan sertifikat.</span>
                </span>
                <i data-lucide="arrow-right" class="h-4 w-4 shrink-0 text-muted-foreground transition group-hover:translate-x-1 group-hover:text-ocean"></i>
            </button>

            <button
                type="button"
                data-chat-topic="wopps"
                class="group flex min-h-28 items-center gap-4 rounded-2xl border border-teal/20 bg-white px-5 py-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-teal hover:shadow-md"
            >
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal/10 text-teal">
                    <i data-lucide="search-check" class="h-5 w-5"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-base font-bold text-navy">WOPPS</span>
                    <span class="mt-1 block text-xs leading-5 text-muted-foreground">Wawancara, observasi, penelitian, permintaan data, dan layanan terkait.</span>
                </span>
                <i data-lucide="arrow-right" class="h-4 w-4 shrink-0 text-muted-foreground transition group-hover:translate-x-1 group-hover:text-teal"></i>
            </button>
        </div>

        <div data-chat-topic-panel="magang" class="mt-8 hidden">
            <div class="mb-3 flex items-center justify-between gap-4 text-left">
                <p class="text-sm font-semibold text-navy">Pilihan pertanyaan Magang / PKL</p>
                <button type="button" data-chat-topic-reset class="text-xs font-semibold text-ocean hover:underline">Ubah topik</button>
            </div>
            <div class="grid gap-3 text-left sm:grid-cols-2">
                @foreach ([
                    'Apa saja persyaratan pengajuan magang?',
                    'Bagaimana alur pengajuan Magang / PKL?',
                    'Bagaimana ketentuan peserta selama magang?',
                    'Bagaimana penerbitan surat keterangan dan sertifikat?',
                ] as $question)
                    <button type="button" data-chat-suggested="{{ $question }}" class="group flex min-h-20 items-center justify-between gap-4 rounded-2xl border border-border bg-white px-4 py-3 text-sm font-medium leading-5 text-navy shadow-sm transition hover:-translate-y-0.5 hover:border-ocean/30 hover:shadow-md">
                        <span>{{ $question }}</span>
                        <i data-lucide="arrow-right" class="h-4 w-4 shrink-0 text-muted-foreground transition group-hover:translate-x-1 group-hover:text-ocean"></i>
                    </button>
                @endforeach
            </div>
        </div>

        <div data-chat-topic-panel="wopps" class="mt-8 hidden">
            <div class="mb-3 flex items-center justify-between gap-4 text-left">
                <p class="text-sm font-semibold text-navy">Pilihan pertanyaan WOPPS</p>
                <button type="button" data-chat-topic-reset class="text-xs font-semibold text-ocean hover:underline">Ubah topik</button>
            </div>
            <div class="grid gap-3 text-left sm:grid-cols-2">
                @foreach ([
                    'Apa saja persyaratan pengajuan penelitian atau permintaan data?',
                    'Bagaimana cara mengajukan wawancara atau observasi?',
                    'Dokumen apa saja yang perlu disiapkan untuk WOPPS?',
                    'Ke mana saya mengirimkan dokumen pengajuan?',
                ] as $question)
                    <button type="button" data-chat-suggested="{{ $question }}" class="group flex min-h-20 items-center justify-between gap-4 rounded-2xl border border-border bg-white px-4 py-3 text-sm font-medium leading-5 text-navy shadow-sm transition hover:-translate-y-0.5 hover:border-teal/30 hover:shadow-md">
                        <span>{{ $question }}</span>
                        <i data-lucide="arrow-right" class="h-4 w-4 shrink-0 text-muted-foreground transition group-hover:translate-x-1 group-hover:text-teal"></i>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mx-auto mt-7 flex max-w-xl items-start gap-3 rounded-2xl border border-ocean/10 bg-secondary/60 p-4 text-left">
            <i data-lucide="info" class="mt-0.5 h-5 w-5 shrink-0 text-ocean"></i>
            <p class="text-xs leading-5 text-muted-foreground sm:text-sm">
                Jika informasi tidak tersedia di knowledge base resmi, Si-Molek akan menyampaikan bahwa informasi belum ditemukan dan tidak akan mengarang jawaban.
            </p>
        </div>
    </div>
</section>
