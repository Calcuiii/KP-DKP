<nav class="sticky top-0 z-50 border-b border-border bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">

            {{-- Logo / Institution --}}
            <a href="#beranda" class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean">
                    <i
                        data-lucide="fish"
                        class="h-5 w-5 text-white"
                        aria-hidden="true"
                    ></i>
                </div>

                <div>
                    <div class="text-sm font-bold leading-tight text-navy">
                        Dinas Kelautan dan Perikanan
                    </div>

                    <div class="text-xs leading-tight text-muted-foreground">
                        Provinsi Jawa Timur
                    </div>
                </div>
            </a>

            {{-- Desktop Navigation --}}
            <div class="hidden items-center gap-6 md:flex">
                <a
                    href="#beranda"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-ocean"
                >
                    Beranda
                </a>

                <a
                    href="#layanan"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-ocean"
                >
                    Layanan
                </a>

                <a
                    href="#informasi"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-ocean"
                >
                    Informasi
                </a>

                <a
                    href="#cara-kerja"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-ocean"
                >
                    Cara Kerja
                </a>

                <a
                    href="#faq"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-ocean"
                >
                    FAQ
                </a>

                <a
                    href="#tentang"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-ocean"
                >
                    Tentang
                </a>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('chatbot') }}"
                    class="hidden items-center gap-2 rounded-xl bg-ocean px-4 py-2 text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-95 md:inline-flex"
                >
                    <i
                        data-lucide="message-square"
                        class="h-[15px] w-[15px]"
                        aria-hidden="true"
                    ></i>

                    Mulai Bertanya
                </a>

                <button
                    type="button"
                    data-mobile-menu-button
                    aria-label="Buka menu navigasi"
                    aria-expanded="false"
                    aria-controls="mobile-navigation"
                    class="p-2 md:hidden"
                >
                    <i
                        data-lucide="menu"
                        data-menu-icon
                        class="h-5 w-5"
                        aria-hidden="true"
                    ></i>

                    <i
                        data-lucide="x"
                        data-close-icon
                        class="hidden h-5 w-5"
                        aria-hidden="true"
                    ></i>
                </button>
            </div>

        </div>
    </div>

    {{-- Mobile Navigation --}}
    <div
        id="mobile-navigation"
        data-mobile-menu
        class="hidden border-t border-border bg-white px-4 py-3 md:hidden"
    >
        <div class="space-y-2">
            <a
                href="#beranda"
                class="block py-2 text-sm font-medium text-muted-foreground"
            >
                Beranda
            </a>

            <a
                href="#layanan"
                class="block py-2 text-sm font-medium text-muted-foreground"
            >
                Layanan
            </a>

            <a
                href="#informasi"
                class="block py-2 text-sm font-medium text-muted-foreground"
            >
                Informasi
            </a>

            <a
                href="#cara-kerja"
                class="block py-2 text-sm font-medium text-muted-foreground"
            >
                Cara Kerja
            </a>

            <a
                href="#faq"
                class="block py-2 text-sm font-medium text-muted-foreground"
            >
                FAQ
            </a>

            <a
                href="#tentang"
                class="block py-2 text-sm font-medium text-muted-foreground"
            >
                Tentang
            </a>

            <a
                href="{{ route('chatbot') }}"
                class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-ocean py-2.5 text-sm font-semibold text-white"
            >
                <i
                    data-lucide="message-square"
                    class="h-[15px] w-[15px]"
                    aria-hidden="true"
                ></i>

                Mulai Bertanya
            </a>
        </div>
    </div>
</nav>

