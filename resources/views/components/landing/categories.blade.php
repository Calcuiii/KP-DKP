<section id="layanan" class="bg-background py-16 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <span class="text-sm font-semibold text-teal">Layanan dalam satu platform</span>
            <h2 class="mt-2 text-3xl font-bold text-navy sm:text-4xl">Pilih layanan sesuai kebutuhan Anda</h2>
            <p class="mt-4 leading-relaxed text-muted-foreground">
                Mulai dari persiapan pengajuan hingga pencarian informasi, setiap fitur SI-MELAYUR
                dirancang untuk mendampingi perjalanan peserta.
            </p>
        </div>

        <div class="mt-10 grid gap-5 lg:grid-cols-3">
            <a href="{{ auth('peserta')->check() ? route('peserta.dashboard') : route('peserta.register') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-navy to-ocean p-7 text-white shadow-lg shadow-ocean/10 transition hover:-translate-y-1 hover:shadow-xl">
                <span class="absolute -right-12 -top-12 h-36 w-36 rounded-full border-[24px] border-white/5"></span>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15"><i data-lucide="clipboard-list" class="h-6 w-6"></i></span>
                        <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold">LAYANAN UTAMA</span>
                    </div>
                    <h3 class="mt-8 text-xl font-bold">Pengajuan &amp; Dashboard Peserta</h3>
                    <p class="mt-3 text-sm leading-relaxed text-blue-100">Buat akun, siapkan data pengajuan, dan lanjutkan proses layanan peserta secara terarah.</p>
                    <span class="mt-7 inline-flex items-center gap-2 text-sm font-semibold">Mulai layanan <i data-lucide="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1"></i></span>
                </div>
            </a>

            <a href="{{ route('infographics') }}" class="group rounded-3xl border border-border bg-white p-7 transition hover:-translate-y-1 hover:border-teal/30 hover:shadow-xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal/10 text-teal"><i data-lucide="images" class="h-6 w-6"></i></div>
                <h3 class="mt-8 text-xl font-bold text-navy">Infografis &amp; Panduan</h3>
                <p class="mt-3 text-sm leading-relaxed text-muted-foreground">Pelajari alur, persyaratan, dokumen, dan informasi penting melalui materi visual resmi.</p>
                <span class="mt-7 inline-flex items-center gap-2 text-sm font-semibold text-teal">Lihat panduan <i data-lucide="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1"></i></span>
            </a>

            <a href="{{ route('chatbot') }}" class="group rounded-3xl border border-border bg-white p-7 transition hover:-translate-y-1 hover:border-ocean/30 hover:shadow-xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-ocean/10 text-ocean"><i data-lucide="message-square" class="h-6 w-6"></i></div>
                <h3 class="mt-8 text-xl font-bold text-navy">Asisten SI-MELAYUR</h3>
                <p class="mt-3 text-sm leading-relaxed text-muted-foreground">Butuh jawaban cepat? Tanyakan persyaratan atau prosedur berdasarkan dokumen resmi yang tersedia.</p>
                <span class="mt-7 inline-flex items-center gap-2 text-sm font-semibold text-ocean">Tanya asisten <i data-lucide="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1"></i></span>
            </a>
        </div>

        <div class="mt-6 flex flex-col gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 sm:flex-row sm:items-center">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700"><i data-lucide="info" class="h-5 w-5"></i></span>
            <p class="text-sm leading-relaxed text-amber-950"><strong>Catatan:</strong> Asisten SI-MELAYUR membantu pencarian informasi. Pengajuan dan pengelolaan layanan tetap dilakukan melalui akun serta dashboard peserta.</p>
        </div>
    </div>
</section>
