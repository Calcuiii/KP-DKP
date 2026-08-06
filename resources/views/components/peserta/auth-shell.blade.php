@props([
    'eyebrow' => 'Portal Peserta',
    'title',
    'description',
])

<div class="participant-auth-scene min-h-screen px-4 py-6 font-sans sm:px-8 sm:py-10 lg:flex lg:items-center lg:justify-center lg:px-10">
    <div class="participant-auth-card mx-auto grid min-h-[calc(100vh-3rem)] w-full max-w-7xl overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-[0_28px_80px_rgba(12,35,64,0.16)] lg:min-h-[42rem] lg:grid-cols-[56fr_44fr] lg:rounded-[2.25rem]">
        <aside class="relative hidden overflow-hidden bg-light px-9 py-8 lg:flex lg:flex-col xl:px-12">
            <div class="absolute -right-24 top-[-7rem] h-[28rem] w-[28rem] rounded-full bg-ocean/[0.07]"></div>
            <div class="absolute bottom-[-10rem] left-[-10rem] h-[27rem] w-[27rem] rounded-full bg-teal/[0.08]"></div>
            <div class="absolute bottom-0 right-0 h-2/3 w-1/2 bg-gradient-to-tl from-ocean/[0.08] to-transparent"></div>

            <a href="{{ route('landing') }}" class="relative z-10 inline-flex w-fit items-center gap-3 text-navy">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-navy text-white shadow-lg shadow-navy/15">
                    <i data-lucide="fish" class="h-5 w-5" aria-hidden="true"></i>
                </span>
                <span>
                    <span class="block text-base font-extrabold leading-none">Si-Molek</span>
                    <span class="mt-1 block text-[11px] font-medium text-muted-foreground">Portal Peserta DKP Jatim</span>
                </span>
            </a>

            <div class="relative z-10 flex flex-1 flex-col justify-center pb-6 pt-12">
                <p class="auth-fade-up text-xs font-bold uppercase tracking-[0.2em] text-teal">Layanan pendampingan resmi</p>
                <h2 class="auth-fade-up auth-delay-1 mt-4 max-w-md text-4xl font-extrabold leading-[1.12] tracking-tight text-navy xl:text-5xl">Perjalanan Magang dan PKL yang lebih terarah.</h2>
                <p class="auth-fade-up auth-delay-2 mt-5 max-w-md text-sm leading-relaxed text-muted-foreground">Simpan akses layanan Anda dan temukan informasi resmi DKP Jawa Timur di satu portal.</p>

                <div class="auth-fade-up auth-delay-3 relative mt-8 max-w-xl overflow-hidden rounded-[1.75rem] border border-ocean/10 bg-white/70 p-5 shadow-xl shadow-navy/[0.06] backdrop-blur-sm">
                    <svg viewBox="0 0 560 300" class="h-auto w-full" role="img" aria-label="Ilustrasi kapal, mercusuar, dan laut">
                        <defs>
                            <linearGradient id="participant-sky" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#E8F0FB" />
                                <stop offset="100%" stop-color="#FFFFFF" />
                            </linearGradient>
                            <linearGradient id="participant-sea" x1="0" x2="1" y1="0" y2="0">
                                <stop offset="0%" stop-color="#1A5FA8" />
                                <stop offset="100%" stop-color="#0D9E8A" />
                            </linearGradient>
                        </defs>
                        <rect width="560" height="300" rx="24" fill="url(#participant-sky)" />
                        <circle cx="440" cy="62" r="28" fill="#38BDF8" opacity=".22" />
                        <path class="participant-auth-wave" d="M0 204c70-38 130 34 214-4s153-44 228-3 80-8 118-26v129H0z" fill="url(#participant-sea)" opacity=".94" />
                        <path d="M0 234c67-31 126 27 207-4s155-39 230-2 84-13 123-4" fill="none" stroke="#FFFFFF" stroke-linecap="round" stroke-width="7" opacity=".72" />
                        <path d="M74 232h129l-18 22h-92z" fill="#0C2340" />
                        <path d="M122 181h7v51h-7z" fill="#0C2340" />
                        <path d="M129 182l56 25h-56z" fill="#1A5FA8" />
                        <path d="M386 86h48l-8 18h-32z" fill="#0C2340" />
                        <path d="M397 104h26l17 100h-60z" fill="#FFFFFF" stroke="#0C2340" stroke-width="7" />
                        <path d="M390 140h40M387 171h47" stroke="#1A5FA8" stroke-width="8" />
                        <rect x="388" y="187" width="44" height="17" rx="3" fill="#0C2340" />
                        <path d="M405 72V43" stroke="#0C2340" stroke-linecap="round" stroke-width="6" />
                        <path d="M377 51h57" stroke="#F8C04F" stroke-linecap="round" stroke-width="13" />
                        <path d="M61 89c16-16 33-16 49 0M95 64c11-11 23-11 34 0" fill="none" stroke="#5B7A9D" stroke-linecap="round" stroke-width="5" />
                    </svg>
                </div>
            </div>

            <p class="relative z-10 text-xs text-muted-foreground">© {{ now()->year }} Dinas Kelautan dan Perikanan Provinsi Jawa Timur</p>
        </aside>

        <main class="relative flex items-center justify-center overflow-hidden bg-gradient-to-br from-navy via-navy to-ocean px-5 py-10 sm:px-10 lg:px-12">
            <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-cyan/20 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-28 h-72 w-72 rounded-full bg-teal/15 blur-3xl"></div>

            <div class="auth-fade-up relative z-10 w-full max-w-md rounded-[1.75rem] bg-white p-6 shadow-2xl shadow-navy/25 sm:p-8">
                <a href="{{ route('landing') }}" class="mb-8 inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground transition hover:text-ocean lg:hidden">
                    <i data-lucide="arrow-left" class="h-4 w-4" aria-hidden="true"></i>
                    Kembali ke Si-Molek
                </a>

                <div class="flex items-center gap-3 border-b border-border pb-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-secondary text-ocean">
                        <i data-lucide="user-round" class="h-5 w-5" aria-hidden="true"></i>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal">{{ $eyebrow }}</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">Dinas Kelautan dan Perikanan Jatim</p>
                    </div>
                </div>

                <h1 class="mt-7 text-3xl font-extrabold tracking-tight text-navy sm:text-4xl">{{ $title }}</h1>
                <p class="mt-3 text-sm leading-relaxed text-muted-foreground">{{ $description }}</p>

                @if (session('status'))
                    <div class="mt-6 flex gap-3 rounded-2xl border border-teal/25 bg-teal/[0.08] px-4 py-3 text-sm leading-relaxed text-teal" role="status">
                        <i data-lucide="check-circle" class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <div class="mt-7">
                    {{ $slot }}
                </div>

                <div class="mt-7 border-t border-border pt-5 text-xs leading-relaxed text-muted-foreground">
                    <p>Butuh bantuan? Hubungi Bagian Umum dan Kepegawaian DKP Jatim melalui WhatsApp <span class="font-semibold text-ocean">0852-53000-485</span>.</p>
                    <p class="mt-2">Dengan melanjutkan, Anda menyetujui <span class="font-medium text-navy">Syarat &amp; Ketentuan layanan</span>.</p>
                </div>
            </div>
        </main>
    </div>
</div>
