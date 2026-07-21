<header class="flex h-16 shrink-0 items-center gap-3 border-b border-border bg-white px-4 sm:px-6">
    <button
        type="button"
        data-chat-sidebar-open
        class="rounded-xl p-2 text-navy transition hover:bg-secondary lg:hidden"
        aria-label="Buka riwayat percakapan"
        aria-expanded="false"
    >
        <i data-lucide="menu" class="h-5 w-5"></i>
    </button>

    <div class="flex items-center gap-2">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-ocean text-white">
            <i data-lucide="fish" class="h-4 w-4"></i>
        </span>

        <span>
            <span class="block text-sm font-semibold text-navy">DKP Assistant</span>
            <span class="flex items-center gap-1 text-[10px] text-muted-foreground">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-teal"></span>
                Berdasarkan dokumen resmi
            </span>
        </span>
    </div>

    <a
        href="{{ route('landing') }}"
        class="ml-auto inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs text-muted-foreground transition hover:bg-secondary hover:text-navy"
    >
        <i data-lucide="chevron-left" class="h-4 w-4"></i>
        <span class="hidden sm:inline">Beranda</span>
    </a>
</header>
