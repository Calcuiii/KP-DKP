<section
    id="beranda"
    class="mx-auto max-w-7xl px-4 pb-12 pt-16 sm:px-6 lg:px-8 lg:pt-20"
>
    <div class="grid items-center gap-12 lg:grid-cols-2">

        {{-- Hero Content --}}
        <div>
            {{-- Badge --}}
            <span
                class="mb-6 inline-flex items-center gap-1.5 rounded-full bg-ocean/[0.08] px-3 py-1 text-xs font-semibold text-ocean"
            >
                <i
                    data-lucide="zap"
                    class="h-3 w-3"
                    aria-hidden="true"
                ></i>

                Asisten Informasi KP & Magang Berbasis AI
            </span>

            {{-- Heading --}}
            <h1
                class="mb-4 text-4xl font-bold leading-tight text-navy lg:text-5xl"
            >
                Temukan Informasi Kerja Praktik dan Magang

                <span class="relative inline-block">
                    <span class="text-ocean">
                        Lebih Cepat
                    </span>

                    <svg
                        class="absolute -bottom-1 left-0 w-full"
                        height="4"
                        viewBox="0 0 200 4"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M0 2 Q100 0 200 2"
                            stroke="#0D9E8A"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            fill="none"
                        />
                    </svg>
                </span>
            </h1>

            {{-- Description --}}
            <p
                class="mb-8 max-w-lg text-base leading-relaxed text-muted-foreground"
            >
                Tanyakan informasi seputar persyaratan, alur pengajuan,
                dokumen, pelaksanaan, dan layanan Kerja Praktik serta Magang
                melalui chatbot berbasis informasi resmi Dinas Kelautan dan
                Perikanan Provinsi Jawa Timur.
            </p>

            {{-- Actions --}}
            <div class="flex flex-wrap gap-3">

                <a
                    href="#chatbot"
                    class="flex items-center gap-2 rounded-xl bg-gradient-to-br from-ocean to-navy px-6 py-3 font-semibold text-white shadow-md transition-all hover:opacity-90 active:scale-95"
                >
                    <i
                        data-lucide="message-square"
                        class="h-[17px] w-[17px]"
                        aria-hidden="true"
                    ></i>

                    Mulai Bertanya
                </a>

                <a
                    href="#layanan"
                    class="flex items-center gap-2 rounded-xl border border-border px-6 py-3 font-semibold text-foreground transition-all hover:bg-accent"
                >
                    Lihat Layanan

                    <i
                        data-lucide="chevron-right"
                        class="h-4 w-4"
                        aria-hidden="true"
                    ></i>
                </a>

            </div>

            {{-- Feature Highlights --}}
            <div class="mt-10 flex flex-wrap gap-4">

                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ocean text-xs font-bold text-white"
                    >
                        24/7
                    </div>

                    <span class="max-w-[120px] text-sm text-muted-foreground">
                        Akses informasi kapan saja
                    </span>
                </div>

                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ocean text-xs font-bold text-white"
                    >
                        RAG
                    </div>

                    <span class="max-w-[120px] text-sm text-muted-foreground">
                        Jawaban berdasarkan dokumen resmi
                    </span>
                </div>

                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ocean text-[10px] font-bold text-white"
                    >
                        Transparan
                    </div>

                    <span class="max-w-[120px] text-sm text-muted-foreground">
                        Sumber jawaban dapat dilihat
                    </span>
                </div>

            </div>
        </div>


        {{-- Chatbot Preview --}}
        <div class="relative">

            <div
                class="overflow-hidden rounded-2xl border border-border bg-white shadow-2xl"
            >

                {{-- Chat Header --}}
                <div
                    class="flex items-center gap-2 border-b border-border bg-navy px-4 py-3"
                >
                    <div
                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15"
                    >
                        <i
                            data-lucide="fish"
                            class="h-3.5 w-3.5 text-[#7EC8FF]"
                            aria-hidden="true"
                        ></i>
                    </div>

                    <div>
                        <span id="chatbot" class="sr-only" aria-hidden="true"></span>
<div class="text-xs font-semibold text-white">
                            DKP Assistant
                        </div>

                        <div class="flex items-center gap-1">
                            <span
                                class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"
                            ></span>

                            <span class="text-[10px] text-blue-200">
                                Online
                            </span>
                        </div>
                    </div>
                </div>


                {{-- Chat Content --}}
                <div class="space-y-4 bg-[#F8FAFC] p-4">

                    {{-- User Message --}}
                    <div class="flex justify-end">
                        <div
                            class="max-w-[75%] rounded-2xl rounded-tr-sm border border-border bg-white px-4 py-2.5 text-sm text-foreground shadow-sm"
                        >
                            Apa saja persyaratan untuk mengajukan magang?
                        </div>
                    </div>


                    {{-- Assistant Message --}}
                    <div class="flex items-start gap-2.5">

                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-ocean"
                        >
                            <i
                                data-lucide="fish"
                                class="h-3.5 w-3.5 text-white"
                                aria-hidden="true"
                            ></i>
                        </div>


                        <div class="min-w-0 flex-1">

                            <div
                                class="rounded-2xl rounded-tl-sm border border-border bg-white px-4 py-3 text-sm leading-relaxed text-foreground shadow-sm"
                            >
                                Berikut persyaratan pengajuan magang berdasarkan
                                informasi resmi yang tersedia:

                                <ol
                                    class="mt-2 list-decimal space-y-1 pl-4 text-muted-foreground"
                                >
                                    <li>
                                        Surat permohonan dari institusi pendidikan
                                    </li>

                                    <li>
                                        Fotokopi KTM / Kartu Siswa aktif
                                    </li>

                                    <li>
                                        CV dan proposal kegiatan magang
                                    </li>

                                    <li>
                                        Transkrip nilai terakhir
                                    </li>
                                </ol>
                            </div>


                            {{-- Source --}}
                            <div
                                class="mt-2 flex items-center gap-1.5 rounded-xl border border-border bg-white px-3 py-2 text-xs text-muted-foreground"
                            >
                                <i
                                    data-lucide="file-text"
                                    class="h-3 w-3 shrink-0 text-teal"
                                    aria-hidden="true"
                                ></i>

                                <span class="font-medium">
                                    SOP Pelayanan KP & Magang
                                </span>

                                <span>
                                    — Halaman 3
                                </span>

                                <span class="ml-auto font-semibold text-teal">
                                    92%
                                </span>
                            </div>

                        </div>
                    </div>
                </div>


                {{-- Chat Input Preview --}}
                <div class="border-t border-border px-4 py-3">

                    <div
                        class="flex items-center gap-2 rounded-xl border border-border bg-input-background px-3 py-2 text-sm text-muted-foreground"
                    >
                        <span class="min-w-0 flex-1 truncate">
                            Tanyakan informasi tentang KP dan Magang...
                        </span>

                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-ocean"
                        >
                            <i
                                data-lucide="send"
                                class="h-[13px] w-[13px] text-white"
                                aria-hidden="true"
                            ></i>
                        </div>
                    </div>

                </div>

            </div>


            {{-- Decorative Elements --}}
            <div
                class="pointer-events-none absolute -right-4 -top-4 -z-10 h-20 w-20 rounded-full opacity-20"
                style="background: radial-gradient(#38BDF8, transparent);"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-4 -left-4 -z-10 h-16 w-16 rounded-full opacity-15"
                style="background: radial-gradient(#0D9E8A, transparent);"
            ></div>

        </div>
    </div>
</section>
