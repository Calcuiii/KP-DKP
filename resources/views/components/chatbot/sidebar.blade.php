<aside
    data-chat-sidebar
    class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col overflow-hidden bg-navy text-white transition-transform duration-300 lg:static lg:w-64 lg:translate-x-0"
>
    <div class="flex h-16 items-center justify-between border-b border-white/10 px-4">
        <a href="{{ route('landing') }}" class="flex min-w-0 items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ocean shadow-sm">
                <i data-lucide="fish" class="h-5 w-5"></i>
            </span>

            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold">DKP Assistant</span>
                <span class="block truncate text-[10px] text-blue-300">Provinsi Jawa Timur</span>
            </span>
        </a>

        <button
            type="button"
            data-chat-sidebar-close
            class="rounded-lg p-2 text-blue-300 transition hover:bg-white/10 hover:text-white lg:hidden"
            aria-label="Tutup sidebar"
        >
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    </div>

    <div class="p-3">
        <button
            type="button"
            data-chat-new
            class="flex w-full items-center gap-2 rounded-xl border border-white/15 px-3 py-2.5 text-sm font-medium transition hover:bg-white/10"
        >
            <i data-lucide="plus" class="h-4 w-4"></i>
            Percakapan Baru
        </button>
    </div>

    <div data-chat-history class="min-h-0 flex-1 overflow-y-auto px-3 pb-4">
        <div data-chat-history-loading class="px-3 py-6 text-center text-xs text-blue-300">
            Memuat riwayat percakapan...
        </div>

        <div data-chat-history-empty class="hidden rounded-xl border border-white/10 px-3 py-4 text-center text-xs leading-5 text-blue-300">
            Belum ada riwayat percakapan di perangkat ini.
        </div>

        <div data-chat-history-list class="space-y-4"></div>
    </div>

    <nav class="border-t border-white/10 p-3">
        <a
            href="{{ route('landing') }}"
            class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs text-blue-300 transition hover:bg-white/10 hover:text-white"
        >
            <i data-lucide="home" class="h-4 w-4"></i>
            Beranda
        </a>

        <a
            href="{{ route('chatbot') }}"
            class="mt-1 flex items-center gap-2.5 rounded-lg bg-white/10 px-3 py-2 text-xs text-white"
            aria-current="page"
        >
            <i data-lucide="message-square" class="h-4 w-4"></i>
            Chatbot
        </a>

        <a
            href="mailto:dkp@jatimprov.go.id"
            class="mt-1 flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs text-blue-300 transition hover:bg-white/10 hover:text-white"
        >
            <i data-lucide="help-circle" class="h-4 w-4"></i>
            Hubungi Layanan
        </a>

        <div class="mt-3 border-t border-white/10 px-3 pt-3 text-[10px] leading-5 text-blue-400">
            Jawaban hanya menggunakan knowledge base resmi yang tersedia.
        </div>
    </nav>
</aside>
