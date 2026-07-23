<section id="layanan" class="bg-background py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <div class="mb-10 text-center">
            <span
                class="mb-4 inline-flex items-center gap-1.5 rounded-full bg-ocean/[0.08] px-3 py-1 text-xs font-semibold text-ocean"
            >
                Layanan Informasi
            </span>

            <h2 class="mb-3 text-3xl font-bold text-navy">
                Informasi yang Bisa Ditanyakan Melalui Chatbot
            </h2>

            <p class="mx-auto max-w-xl text-muted-foreground">
                Pilih kategori informasi Kerja Praktik dan Magang yang paling
                sering dibutuhkan.
            </p>
        </div>


        {{-- Categories Grid --}}
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">

            {{-- Persyaratan --}}
            <a
                href="{{ route('chatbot') }}"
                class="group rounded-2xl border border-border bg-white p-5 text-left transition-all hover:border-ocean/30 hover:shadow-md"
            >
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-ocean/[0.08]">
                    <i
                        data-lucide="shield"
                        class="h-5 w-5 text-ocean"
                        aria-hidden="true"
                    ></i>
                </div>

                <h3 class="mb-1.5 text-sm font-semibold text-navy">
                    Persyaratan KP & Magang
                </h3>

                <p class="mb-3 text-xs leading-relaxed text-muted-foreground">
                    Informasi lengkap tentang syarat akademik, administrasi,
                    dan kelengkapan dokumen.
                </p>

                <div class="mb-3 border-l-2 border-ocean pl-2 text-xs italic text-muted-foreground">
                    "Apa saja persyaratan pengajuan magang?"
                </div>

                <span class="inline-flex items-center gap-1 text-xs font-semibold text-ocean transition-all group-hover:gap-2">
                    Tanyakan ke Chatbot

                    <i
                        data-lucide="arrow-right"
                        class="h-3 w-3"
                        aria-hidden="true"
                    ></i>
                </span>
            </a>


            {{-- Alur Pengajuan --}}
            <a
                href="{{ route('chatbot') }}"
                class="group rounded-2xl border border-border bg-white p-5 text-left transition-all hover:border-teal/30 hover:shadow-md"
            >
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-teal/[0.08]">
                    <i
                        data-lucide="arrow-right"
                        class="h-5 w-5 text-teal"
                        aria-hidden="true"
                    ></i>
                </div>

                <h3 class="mb-1.5 text-sm font-semibold text-navy">
                    Alur Pengajuan
                </h3>

                <p class="mb-3 text-xs leading-relaxed text-muted-foreground">
                    Panduan langkah-langkah proses pengajuan dari awal hingga
                    persetujuan.
                </p>

                <div class="mb-3 border-l-2 border-teal pl-2 text-xs italic text-muted-foreground">
                    "Bagaimana alur pengajuan KP?"
                </div>

                <span class="inline-flex items-center gap-1 text-xs font-semibold text-teal transition-all group-hover:gap-2">
                    Tanyakan ke Chatbot

                    <i
                        data-lucide="arrow-right"
                        class="h-3 w-3"
                        aria-hidden="true"
                    ></i>
                </span>
            </a>


            {{-- Dokumen Pengajuan --}}
            <a
                href="{{ route('chatbot') }}"
                class="group rounded-2xl border border-border bg-white p-5 text-left transition-all hover:border-indigo-500/30 hover:shadow-md"
            >
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/[0.08]">
                    <i
                        data-lucide="file-text"
                        class="h-5 w-5 text-indigo-500"
                        aria-hidden="true"
                    ></i>
                </div>

                <h3 class="mb-1.5 text-sm font-semibold text-navy">
                    Dokumen Pengajuan
                </h3>

                <p class="mb-3 text-xs leading-relaxed text-muted-foreground">
                    Daftar dokumen yang wajib disiapkan dan cara
                    pengumpulannya.
                </p>

                <div class="mb-3 border-l-2 border-indigo-500 pl-2 text-xs italic text-muted-foreground">
                    "Dokumen apa saja yang harus disiapkan?"
                </div>

                <span class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-500 transition-all group-hover:gap-2">
                    Tanyakan ke Chatbot

                    <i
                        data-lucide="arrow-right"
                        class="h-3 w-3"
                        aria-hidden="true"
                    ></i>
                </span>
            </a>


            {{-- Pelaksanaan --}}
            <a
                href="{{ route('chatbot') }}"
                class="group rounded-2xl border border-border bg-white p-5 text-left transition-all hover:border-amber-500/30 hover:shadow-md"
            >
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/[0.08]">
                    <i
                        data-lucide="book-open"
                        class="h-5 w-5 text-amber-500"
                        aria-hidden="true"
                    ></i>
                </div>

                <h3 class="mb-1.5 text-sm font-semibold text-navy">
                    Pelaksanaan KP & Magang
                </h3>

                <p class="mb-3 text-xs leading-relaxed text-muted-foreground">
                    Informasi tentang hak, kewajiban, dan tata tertib selama
                    pelaksanaan.
                </p>

                <div class="mb-3 border-l-2 border-amber-500 pl-2 text-xs italic text-muted-foreground">
                    "Apa kewajiban peserta magang selama pelaksanaan?"
                </div>

                <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-500 transition-all group-hover:gap-2">
                    Tanyakan ke Chatbot

                    <i
                        data-lucide="arrow-right"
                        class="h-3 w-3"
                        aria-hidden="true"
                    ></i>
                </span>
            </a>


            {{-- Penyelesaian --}}
            <a
                href="{{ route('chatbot') }}"
                class="group rounded-2xl border border-border bg-white p-5 text-left transition-all hover:border-pink-500/30 hover:shadow-md"
            >
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-pink-500/[0.08]">
                    <i
                        data-lucide="check-circle"
                        class="h-5 w-5 text-pink-500"
                        aria-hidden="true"
                    ></i>
                </div>

                <h3 class="mb-1.5 text-sm font-semibold text-navy">
                    Penyelesaian Kegiatan
                </h3>

                <p class="mb-3 text-xs leading-relaxed text-muted-foreground">
                    Panduan pelaporan, evaluasi, dan prosedur mengakhiri
                    kegiatan KP/Magang.
                </p>

                <div class="mb-3 border-l-2 border-pink-500 pl-2 text-xs italic text-muted-foreground">
                    "Bagaimana prosedur penyelesaian KP?"
                </div>

                <span class="inline-flex items-center gap-1 text-xs font-semibold text-pink-500 transition-all group-hover:gap-2">
                    Tanyakan ke Chatbot

                    <i
                        data-lucide="arrow-right"
                        class="h-3 w-3"
                        aria-hidden="true"
                    ></i>
                </span>
            </a>


            {{-- Sertifikat --}}
            <a
                href="{{ route('chatbot') }}"
                class="group rounded-2xl border border-border bg-white p-5 text-left transition-all hover:border-sky-500/30 hover:shadow-md"
            >
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/[0.08]">
                    <i
                        data-lucide="award"
                        class="h-5 w-5 text-sky-500"
                        aria-hidden="true"
                    ></i>
                </div>

                <h3 class="mb-1.5 text-sm font-semibold text-navy">
                    Sertifikat & Administrasi Akhir
                </h3>

                <p class="mb-3 text-xs leading-relaxed text-muted-foreground">
                    Informasi pengurusan sertifikat dan dokumen administratif
                    setelah selesai.
                </p>

                <div class="mb-3 border-l-2 border-sky-500 pl-2 text-xs italic text-muted-foreground">
                    "Bagaimana cara mendapatkan sertifikat magang?"
                </div>

                <span class="inline-flex items-center gap-1 text-xs font-semibold text-sky-500 transition-all group-hover:gap-2">
                    Tanyakan ke Chatbot

                    <i
                        data-lucide="arrow-right"
                        class="h-3 w-3"
                        aria-hidden="true"
                    ></i>
                </span>
            </a>

        </div>
    </div>
</section>


