<footer class="border-t border-border bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mb-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">

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
                            DKP Assistant
                        </div>

                        <div class="text-[10px] leading-tight text-muted-foreground">
                            Jawa Timur
                        </div>
                    </div>
                </div>

                <p class="mt-3 max-w-[180px] text-xs leading-relaxed text-muted-foreground">
                    Layanan chatbot AI untuk informasi Kerja Praktik dan Magang berbasis dokumen resmi.
                </p>
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
                    href="#chatbot"
                    class="block py-1 text-xs text-muted-foreground hover:text-foreground"
                >
                    Chatbot
                </a>

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

            <div class="flex gap-4">
                <span class="text-xs text-muted-foreground">
                    Kebijakan Privasi
                </span>

                <span class="text-xs text-muted-foreground">
                    Disclaimer
                </span>
            </div>

        </div>
    </div>
</footer>
