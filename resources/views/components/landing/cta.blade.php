<section id="kontak-layanan" class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
            class="rounded-3xl px-6 py-12 text-center sm:px-12"
            style="background: linear-gradient(135deg, #0C2340 0%, #1A5FA8 100%);"
        >
            <h2 class="mb-3 text-3xl font-bold text-white">
                Siap Memulai Layanan Anda di Si-Molek?
            </h2>

            <p class="mx-auto mb-8 max-w-lg text-blue-200">
                Buat akun peserta untuk mempersiapkan pengajuan, atau gunakan Asisten Si-Molek jika Anda masih membutuhkan informasi.
            </p>

            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ auth('peserta')->check() ? route('peserta.dashboard') : route('peserta.register') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-7 py-3.5 text-sm font-semibold text-navy transition hover:bg-blue-50">
                    <i data-lucide="file-check" class="h-4 w-4" aria-hidden="true"></i>
                    {{ auth('peserta')->check() ? 'Buka Dashboard Saya' : 'Daftar sebagai Peserta' }}
                </a>
                <a href="{{ route('chatbot') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/30 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">
                    <i data-lucide="message-square" class="h-4 w-4" aria-hidden="true"></i>
                    Tanya Asisten
                </a>
            </div>
        </div>
    </div>
</section>
