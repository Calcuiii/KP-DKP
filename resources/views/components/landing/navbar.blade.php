<nav class="sticky top-0 z-50 border-b border-border bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">

            {{-- Logo / Institution --}}
            <a href="{{ route('landing') }}#beranda" class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean">
                    <i
                        data-lucide="fish"
                        class="h-5 w-5 text-white"
                        aria-hidden="true"
                    ></i>
                </div>

                <div>
                    <div class="text-sm font-bold leading-tight text-navy">
                        Si-Molek
                    </div>

                    <div class="text-xs leading-tight text-muted-foreground">
                        Layanan KP, Magang, PKL &amp; WOPPS
                    </div>
                </div>
            </a>

            {{-- Desktop Navigation --}}
            <div class="hidden items-center gap-6 lg:flex">
                <a
                    href="{{ route('landing') }}#beranda"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-ocean"
                >
                    Beranda
                </a>

                <a
                    href="{{ route('landing') }}#layanan"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-ocean"
                >
                    Layanan
                </a>

                <a
                    href="{{ route('landing') }}#faq"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-ocean"
                >
                    FAQ
                </a>

                <a
                    href="{{ route('infographics') }}"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-ocean"
                >
                    Infografis
                </a>

                <a href="{{ route('chatbot') }}" class="text-sm font-medium text-muted-foreground transition-colors hover:text-ocean">
                    Asisten
                </a>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="hidden items-center gap-2 lg:flex">
                    @auth('peserta')
                        <a
                            href="{{ route('peserta.dashboard') }}"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-ocean/25 px-3 py-2 text-sm font-semibold text-ocean transition hover:bg-ocean/5"
                        >
                            <i data-lucide="user-round" class="h-4 w-4" aria-hidden="true"></i>
                            Dashboard Saya
                        </a>
                    @else
                        <a
                            href="{{ route('peserta.login') }}"
                            class="px-2 py-2 text-sm font-semibold text-muted-foreground transition hover:text-ocean"
                        >
                            Masuk
                        </a>

                        <a
                            href="{{ route('peserta.register') }}"
                            class="rounded-xl border border-ocean/30 px-3 py-2 text-sm font-semibold text-ocean transition hover:bg-ocean/5"
                        >
                            Daftar
                        </a>
                    @endauth

                </div>

                <button
                    type="button"
                    data-mobile-menu-button
                    aria-label="Buka menu navigasi"
                    aria-expanded="false"
                    aria-controls="mobile-navigation"
                    class="p-2 lg:hidden"
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
        class="hidden border-t border-border bg-white px-4 py-3 lg:hidden"
    >
        <div class="space-y-2">
            <a
                href="{{ route('landing') }}#beranda"
                class="block py-2 text-sm font-medium text-muted-foreground"
            >
                Beranda
            </a>

            <a
                href="{{ route('landing') }}#layanan"
                class="block py-2 text-sm font-medium text-muted-foreground"
            >
                Layanan
            </a>

            <a
                href="{{ route('landing') }}#faq"
                class="block py-2 text-sm font-medium text-muted-foreground"
            >
                FAQ
            </a>

            <a
                href="{{ route('infographics') }}"
                class="block py-2 text-sm font-medium text-muted-foreground"
            >
                Infografis
            </a>

            <a href="{{ route('chatbot') }}" class="block py-2 text-sm font-medium text-muted-foreground">
                Asisten Si-Molek
            </a>

            @auth('peserta')
                <a
                    href="{{ route('peserta.dashboard') }}"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-ocean/30 py-2.5 text-sm font-semibold text-ocean"
                >
                    <i data-lucide="user-round" class="h-[15px] w-[15px]" aria-hidden="true"></i>
                    Dashboard Saya
                </a>
            @else
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a
                        href="{{ route('peserta.login') }}"
                        class="flex items-center justify-center rounded-xl border border-border py-2.5 text-sm font-semibold text-navy"
                    >
                        Masuk Peserta
                    </a>

                    <a
                        href="{{ route('peserta.register') }}"
                        class="flex items-center justify-center rounded-xl bg-navy py-2.5 text-sm font-semibold text-white"
                    >
                        Daftar Akun
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>
