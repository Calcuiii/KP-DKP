<section
    data-chat-empty
    class="flex min-h-0 flex-1 overflow-y-auto px-4 py-8 sm:px-6"
>
    <div class="m-auto w-full max-w-2xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-ocean text-white shadow-md">
            <i data-lucide="fish" class="h-8 w-8"></i>
        </div>

        <h1 class="mt-5 text-2xl font-bold tracking-tight text-navy sm:text-3xl">
            Halo, ada yang bisa DKP Assistant bantu?
        </h1>

        <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-muted-foreground sm:text-base">
            Tanyakan informasi seputar Kerja Praktik dan Magang berdasarkan dokumen resmi Dinas Kelautan dan Perikanan Provinsi Jawa Timur.
        </p>

        <div class="mt-8 grid gap-3 text-left sm:grid-cols-2">
            @foreach ([
                'Apa saja persyaratan pengajuan magang?',
                'Bagaimana alur pengajuan KP?',
                'Berapa lama proses pengajuan?',
                'Dokumen apa saja yang harus disiapkan?',
            ] as $question)
                <button
                    type="button"
                    data-chat-suggested="{{ $question }}"
                    class="group flex min-h-20 items-center justify-between gap-4 rounded-2xl border border-border bg-white px-4 py-3 text-sm font-medium leading-5 text-navy shadow-sm transition hover:-translate-y-0.5 hover:border-ocean/30 hover:shadow-md"
                >
                    <span>{{ $question }}</span>
                    <i data-lucide="arrow-right" class="h-4 w-4 shrink-0 text-muted-foreground transition group-hover:translate-x-1 group-hover:text-ocean"></i>
                </button>
            @endforeach
        </div>

        <div class="mx-auto mt-7 flex max-w-xl items-start gap-3 rounded-2xl border border-ocean/10 bg-secondary/60 p-4 text-left">
            <i data-lucide="info" class="mt-0.5 h-5 w-5 shrink-0 text-ocean"></i>
            <p class="text-xs leading-5 text-muted-foreground sm:text-sm">
                Jika informasi tidak tersedia di knowledge base resmi, DKP Assistant akan menyampaikan bahwa informasi belum ditemukan dan tidak akan mengarang jawaban.
            </p>
        </div>
    </div>
</section>
