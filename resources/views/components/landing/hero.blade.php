<section id="beranda" class="relative overflow-hidden bg-white">
    <div class="absolute inset-x-0 top-0 h-full bg-[radial-gradient(circle_at_85%_20%,rgba(56,189,248,0.16),transparent_32%),radial-gradient(circle_at_8%_85%,rgba(13,158,138,0.10),transparent_28%)]"></div>

    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 pb-16 pt-14 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:pb-20 lg:pt-20">
        <div>
            <span class="mb-6 inline-flex items-center gap-2 rounded-full border border-ocean/15 bg-ocean/[0.06] px-3 py-1.5 text-xs font-semibold text-ocean">
                <i data-lucide="compass" class="h-3.5 w-3.5" aria-hidden="true"></i>
                Portal Layanan Peserta DKP Jawa Timur
            </span>

            <h1 class="max-w-3xl text-4xl font-bold leading-[1.12] text-navy sm:text-5xl lg:text-[3.5rem]">
                Satu portal untuk layanan
                <span class="text-ocean">magang, penelitian, dan data kelautan</span>
            </h1>

            <p class="mt-6 max-w-2xl text-base leading-relaxed text-muted-foreground sm:text-lg">
                <strong>SI-MELAYUR</strong>—Sistem Informasi Magang, Penelitian, dan Data Kelautan Jawa Timur—membantu Anda menyiapkan pengajuan, mengikuti informasi layanan,
                membaca panduan visual, dan menemukan jawaban dari sumber resmi Dinas Kelautan
                dan Perikanan Provinsi Jawa Timur.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                @auth('peserta')
                    <a href="{{ route('peserta.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-ocean to-navy px-6 py-3 font-semibold text-white shadow-md transition hover:opacity-90">
                        <i data-lucide="home" class="h-4 w-4" aria-hidden="true"></i>
                        Buka Dashboard Saya
                    </a>
                @else
                    <a href="{{ route('peserta.register') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-ocean to-navy px-6 py-3 font-semibold text-white shadow-md transition hover:opacity-90">
                        <i data-lucide="file-check" class="h-4 w-4" aria-hidden="true"></i>
                        Daftar akun peserta
                    </a>
                @endauth

                <a href="#layanan" class="inline-flex items-center gap-2 rounded-xl border border-border bg-white px-6 py-3 font-semibold text-navy transition hover:border-ocean/30 hover:bg-secondary">
                    Jelajahi Layanan
                    <i data-lucide="arrow-down" class="h-4 w-4" aria-hidden="true"></i>
                </a>
            </div>

            <div class="mt-9 flex flex-wrap gap-x-6 gap-y-3 text-sm text-muted-foreground">
                <span class="inline-flex items-center gap-2"><i data-lucide="check-circle" class="h-4 w-4 text-teal"></i> Informasi resmi</span>
                <span class="inline-flex items-center gap-2"><i data-lucide="check-circle" class="h-4 w-4 text-teal"></i> Proses terarah</span>
                <span class="inline-flex items-center gap-2"><i data-lucide="check-circle" class="h-4 w-4 text-teal"></i> Akses mandiri</span>
            </div>
        </div>

        <div class="relative mx-auto w-full max-w-xl">
            <div class="rounded-[2rem] border border-white/80 bg-white/85 p-5 shadow-2xl shadow-ocean/15 backdrop-blur sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-ocean">Pusat Layanan SI-MELAYUR</p><h2 class="mt-2 text-xl font-bold text-navy">Apa yang ingin Anda lakukan?</h2></div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-navy text-white"><i data-lucide="fish" class="h-5 w-5" aria-hidden="true"></i></div>
                </div>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <a href="{{ auth('peserta')->check() ? route('peserta.dashboard') : route('peserta.register') }}" class="group rounded-2xl bg-navy p-5 text-white transition hover:-translate-y-0.5 hover:shadow-lg sm:col-span-2">
                        <div class="flex items-center justify-between"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15"><i data-lucide="file-check" class="h-5 w-5"></i></span><i data-lucide="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1"></i></div>
                        <h3 class="mt-5 font-bold">Layanan Pengajuan Peserta</h3><p class="mt-1 text-sm leading-relaxed text-blue-100">Siapkan data dan kelola proses KP, Magang, PKL, atau WOPPS Anda.</p>
                    </a>
                    <a href="{{ route('infographics') }}" class="group rounded-2xl border border-border bg-background p-4 transition hover:-translate-y-0.5 hover:border-teal/30 hover:shadow-md"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal/10 text-teal"><i data-lucide="book-open" class="h-4 w-4"></i></span><h3 class="mt-4 text-sm font-bold text-navy">Panduan &amp; Infografis</h3><p class="mt-1 text-xs leading-relaxed text-muted-foreground">Pahami prosedur lewat panduan visual.</p></a>
                    <a href="{{ route('chatbot') }}" class="group rounded-2xl border border-border bg-background p-4 transition hover:-translate-y-0.5 hover:border-ocean/30 hover:shadow-md"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean/10 text-ocean"><i data-lucide="message-square" class="h-4 w-4"></i></span><h3 class="mt-4 text-sm font-bold text-navy">Asisten Informasi</h3><p class="mt-1 text-xs leading-relaxed text-muted-foreground">Tanyakan informasi dari dokumen resmi.</p></a>
                </div>
            </div>
            <div class="absolute -bottom-5 -left-5 -z-10 h-32 w-32 rounded-full bg-teal/15 blur-2xl"></div><div class="absolute -right-6 -top-6 -z-10 h-40 w-40 rounded-full bg-cyan/20 blur-2xl"></div>
        </div>
    </div>
</section>
