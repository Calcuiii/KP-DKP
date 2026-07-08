<section id="cara-kerja" class="bg-background py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <div class="mb-12 text-center">
            <span class="mb-4 inline-flex items-center gap-1.5 rounded-full bg-ocean/[0.08] px-3 py-1 text-xs font-semibold text-ocean">
                Cara Kerja
            </span>

            <h2 class="text-3xl font-bold text-navy">
                Bagaimana DKP Assistant Menghasilkan Jawaban?
            </h2>
        </div>


        {{-- Steps --}}
        <div class="mb-8 flex flex-col items-center gap-4 lg:flex-row">

            {{-- Step 01 --}}
            <div class="flex flex-1 items-center gap-3 lg:flex-col lg:gap-2">
                <div class="flex w-full flex-shrink-0 items-center gap-2 lg:w-auto lg:gap-0">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-ocean shadow-sm">
                        <i data-lucide="message-circle" class="h-[22px] w-[22px] text-white"></i>
                    </div>

                    <div class="mx-3 hidden min-w-5 flex-1 border-t-2 border-dashed border-border lg:block"></div>
                </div>

                <div class="text-center lg:mt-3">
                    <div class="mb-0.5 text-xs font-bold text-ocean">
                        Langkah 01
                    </div>

                    <div class="text-xs font-semibold text-navy">
                        Pengguna Mengajukan Pertanyaan
                    </div>
                </div>
            </div>


            {{-- Step 02 --}}
            <div class="flex flex-1 items-center gap-3 lg:flex-col lg:gap-2">
                <div class="flex w-full flex-shrink-0 items-center gap-2 lg:w-auto lg:gap-0">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-indigo-500 shadow-sm">
                        <i data-lucide="search" class="h-[22px] w-[22px] text-white"></i>
                    </div>

                    <div class="mx-3 hidden min-w-5 flex-1 border-t-2 border-dashed border-border lg:block"></div>
                </div>

                <div class="text-center lg:mt-3">
                    <div class="mb-0.5 text-xs font-bold text-indigo-500">
                        Langkah 02
                    </div>

                    <div class="text-xs font-semibold text-navy">
                        Sistem Mencari Informasi Relevan
                    </div>
                </div>
            </div>


            {{-- Step 03 --}}
            <div class="flex flex-1 items-center gap-3 lg:flex-col lg:gap-2">
                <div class="flex w-full flex-shrink-0 items-center gap-2 lg:w-auto lg:gap-0">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-teal shadow-sm">
                        <i data-lucide="database" class="h-[22px] w-[22px] text-white"></i>
                    </div>

                    <div class="mx-3 hidden min-w-5 flex-1 border-t-2 border-dashed border-border lg:block"></div>
                </div>

                <div class="text-center lg:mt-3">
                    <div class="mb-0.5 text-xs font-bold text-teal">
                        Langkah 03
                    </div>

                    <div class="text-xs font-semibold text-navy">
                        Dokumen Resmi Digunakan sebagai Konteks
                    </div>
                </div>
            </div>


            {{-- Step 04 --}}
            <div class="flex flex-1 items-center gap-3 lg:flex-col lg:gap-2">
                <div class="flex w-full flex-shrink-0 items-center gap-2 lg:w-auto lg:gap-0">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-amber-500 shadow-sm">
                        <i data-lucide="zap" class="h-[22px] w-[22px] text-white"></i>
                    </div>

                    <div class="mx-3 hidden min-w-5 flex-1 border-t-2 border-dashed border-border lg:block"></div>
                </div>

                <div class="text-center lg:mt-3">
                    <div class="mb-0.5 text-xs font-bold text-amber-500">
                        Langkah 04
                    </div>

                    <div class="text-xs font-semibold text-navy">
                        AI Menyusun Jawaban
                    </div>
                </div>
            </div>


            {{-- Step 05 --}}
            <div class="flex flex-1 items-center gap-3 lg:flex-col lg:gap-2">
                <div class="flex w-full flex-shrink-0 items-center gap-2 lg:w-auto lg:gap-0">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-emerald-500 shadow-sm">
                        <i data-lucide="check-circle" class="h-[22px] w-[22px] text-white"></i>
                    </div>
                </div>

                <div class="text-center lg:mt-3">
                    <div class="mb-0.5 text-xs font-bold text-emerald-500">
                        Langkah 05
                    </div>

                    <div class="text-xs font-semibold text-navy">
                        Jawaban dan Sumber Ditampilkan
                    </div>
                </div>
            </div>

        </div>


        {{-- Information Notice --}}
        <div class="mx-auto flex max-w-2xl items-start gap-3 rounded-2xl border border-ocean/20 bg-white p-4">
            <i
                data-lucide="info"
                class="mt-0.5 h-[18px] w-[18px] flex-shrink-0 text-ocean"
                aria-hidden="true"
            ></i>

            <p class="text-sm leading-relaxed text-muted-foreground">
                DKP Assistant hanya memberikan jawaban berdasarkan knowledge base
                yang telah diverifikasi dan dikelola oleh administrator.
            </p>
        </div>

    </div>
</section>
