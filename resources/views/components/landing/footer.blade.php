<footer class="border-t border-border bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mb-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">

            {{-- Brand --}}
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean">
                        <i
                            data-lucide="fish"
                            class="h-5 w-5 text-white"
                            aria-hidden="true"
                        ></i>
                    </div>

                    <div>
                        <div class="text-sm font-bold leading-tight text-navy">
                            SI-MELAYUR
                        </div>

                        <div class="text-[10px] leading-tight text-muted-foreground">
                            Jawa Timur
                        </div>
                    </div>
                </div>

                <p class="mt-3 max-w-64 text-xs leading-relaxed text-muted-foreground">
                    Sistem Informasi Magang, Penelitian, dan Data Kelautan Jawa Timur berbasis dokumen resmi.
                </p>

                <div class="mt-5 flex w-fit items-center gap-4 border-t border-border pt-4" aria-label="Instansi dan perguruan tinggi">
                    <img
                        src="{{ asset('images/brand/dkp-jatim.png') }}"
                        alt="Logo Dinas Kelautan dan Perikanan Provinsi Jawa Timur"
                        width="1254" height="1254" loading="lazy" decoding="async"
                        class="h-11 w-18 shrink-0 object-cover"
                    >
                    <span class="h-7 w-px bg-border" aria-hidden="true"></span>
                    <img
                        src="{{ asset('images/brand/telkom-university-surabaya.png') }}"
                        alt="Logo Telkom University Surabaya"
                        width="677" height="277" loading="lazy" decoding="async"
                        class="h-9 w-auto max-w-28 object-contain"
                    >
                </div>
            </div>


            {{-- Navigation --}}
            <div>
                <h4 class="mb-3 text-sm font-semibold text-navy">
                    Navigasi
                </h4>

                <a
                    href="#beranda"
                    class="block py-1 text-xs text-muted-foreground hover:text-foreground"
                >
                    Beranda
                </a>

                <a
                    href="#layanan"
                    class="block py-1 text-xs text-muted-foreground hover:text-foreground"
                >
                    Layanan
                </a>

                <a
                    href="#cara-kerja"
                    class="block py-1 text-xs text-muted-foreground hover:text-foreground"
                >
                    Cara Kerja
                </a>

                <a
                    href="#faq"
                    class="block py-1 text-xs text-muted-foreground hover:text-foreground"
                >
                    FAQ
                </a>
            </div>


            {{-- Access --}}
            <div>
                <h4 class="mb-3 text-sm font-semibold text-navy">
                    Akses
                </h4>

                <a
                    href="{{ route('chatbot') }}"
                    class="block py-1 text-xs text-muted-foreground hover:text-foreground"
                >
                    Chatbot
                </a>

                @auth('peserta')
                    <a
                        href="{{ route('peserta.dashboard') }}"
                        class="block py-1 text-xs text-muted-foreground hover:text-foreground"
                    >
                        Dashboard Peserta
                    </a>
                @else
                    <a
                        href="{{ route('peserta.login') }}"
                        class="block py-1 text-xs text-muted-foreground hover:text-foreground"
                    >
                        Masuk Peserta
                    </a>

                    <a
                        href="{{ route('peserta.register') }}"
                        class="block py-1 text-xs text-muted-foreground hover:text-foreground"
                    >
                        Daftar Akun Peserta
                    </a>
                @endauth

                <a
                    href="#kontak-layanan"
                    class="block py-1 text-xs text-muted-foreground hover:text-foreground"
                >
                    Kontak Layanan
                </a>
            </div>


            {{-- Institution --}}
            <div>
                <h4 class="mb-3 text-sm font-semibold text-navy">
                    Instansi
                </h4>

                <p class="text-xs leading-relaxed text-muted-foreground">
                    Dinas Kelautan dan Perikanan Provinsi Jawa Timur
                </p>
            </div>

        </div>


        {{-- Footer Bottom --}}
        <div class="flex flex-col items-center justify-between gap-3 border-t border-border pt-6 sm:flex-row">

            <p class="text-xs text-muted-foreground">
                © {{ date('Y') }} Dinas Kelautan dan Perikanan Provinsi Jawa Timur. Hak cipta dilindungi.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
                <span class="text-xs text-muted-foreground">
                    Kebijakan Privasi
                </span>

                <span class="text-xs text-muted-foreground">
                    Disclaimer
                </span>

                <a
                    href="{{ route('admin.login') }}"
                    class="text-xs text-muted-foreground transition hover:text-ocean"
                >
                    Akses Administrator
                </a>
            </div>

        </div>
    </div>
</footer>
