@extends('layouts.app')

@section('title', 'Dashboard | Portal Pendamping DKP Jatim')

@section('hide_dev_nav', true)

@section('content')
    @php
        $participant = auth('peserta')->user();
        $hour = now()->hour;
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));

        // Route Upload/Cek Kelengkapan/Status/Riwayat belum dibuat terpisah oleh tim —
        // sementara diarahkan ke bagian terkait di halaman ini, sampai halamannya jadi.
        $navItems = [
            ['href' => route('peserta.dashboard'), 'icon' => 'layout-grid', 'label' => 'Dashboard', 'active' => true],
            ['href' => '#persiapan', 'icon' => 'upload', 'label' => 'Upload Dokumen', 'active' => false],
            ['href' => '#persiapan', 'icon' => 'clipboard-check', 'label' => 'Cek Kelengkapan', 'active' => false],
            ['href' => '#progress', 'icon' => 'target', 'label' => 'Status Pengajuan', 'active' => false],
            ['href' => '#progress', 'icon' => 'clock', 'label' => 'Riwayat', 'active' => false],
        ];
    @endphp

            <div class="flex min-h-screen bg-[#F4F7FB] font-sans text-navy">

                {{-- ═══════════ SIDEBAR ═══════════ --}}
                <aside class="fixed left-0 top-0 z-40 hidden h-full w-60 flex-col bg-navy md:flex">
<div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
    <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-white shadow-[0_4px_10px_rgba(0,0,0,0.15)]">
        <i
            data-lucide="fish"
            class="h-6 w-6 text-[#0B2545]"
            stroke-width="1.8"
            aria-hidden="true"
        ></i>
    </span>

    <div class="min-w-0">
        <span class="block text-sm font-bold leading-snug text-white">
            Si-Molek
        </span>
        <span class="block text-[10px] font-medium leading-snug text-white/50">
            Portal Peserta DKP Jatim
        </span>
    </div>
</div>

            <nav class="flex flex-1 flex-col gap-0.5 overflow-y-auto px-3 py-4">
                @foreach ($navItems as $item)
                    <a
                        href="{{ $item['href'] }}"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all {{ $item['active'] ? 'bg-ocean/75 text-white shadow-[0_2px_8px_rgba(26,95,168,0.35)]' : 'text-white/55 hover:bg-white/[0.07]' }}"
                    >
                        <i
                            data-lucide="{{ $item['icon'] }}"
                            class="h-[18px] w-[18px] flex-shrink-0 {{ $item['active'] ? 'text-cyan' : '' }}"
                            aria-hidden="true"
                        ></i>

                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-4">
                <div class="mb-3 flex items-center gap-3">
                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-ocean to-teal text-sm font-bold text-white">
                        {{ strtoupper(substr($participant->name, 0, 1)) }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-white">{{ $participant->name }}</span>
                        <span class="block truncate text-xs text-white/40">{{ $participant->email }}</span>
                    </span>
                </div>
                <form method="POST" action="{{ route('peserta.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-xs text-white/40 transition-colors hover:bg-white/[0.06] hover:text-white/75">
                        <i data-lucide="log-out" class="h-3.5 w-3.5" aria-hidden="true"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        {{-- ═══════════ KONTEN UTAMA ═══════════ --}}
        <main class="min-w-0 flex-1 px-4 py-6 md:ml-60 md:px-8 md:py-8">
            <div class="mx-auto max-w-5xl space-y-6">

                @if (session('status'))
                    <div class="flex items-start gap-3 rounded-2xl border border-teal/25 bg-teal/10 px-5 py-4 text-sm font-medium text-teal">
                        <i data-lucide="circle-check" class="mt-0.5 h-5 w-5 flex-shrink-0" aria-hidden="true"></i>
                        {{ session('status') }}
                    </div>
                @endif

                <div>
                    <p class="text-sm font-medium text-muted-foreground">{{ $greeting }},</p>
                    <h1 class="text-2xl font-bold text-navy">{{ $participant->name }} 👋</h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ $application ? 'Anda sedang berada pada tahap ' . strtolower($application->serviceLabel()) : 'Pilih jenis layanan yang ingin Anda ajukan' }}
                    </p>
                </div>

                @if (! $application)
                    {{-- ═══ BELUM ADA PENGAJUAN AKTIF ═══ --}}

                    <div class="flex flex-col items-center gap-5 rounded-2xl border border-ocean/10 bg-gradient-to-br from-[#EDF3FB] to-[#E8F5F3] p-6 sm:flex-row">
                        <svg width="120" height="72" viewBox="0 0 200 120" fill="none" aria-hidden="true" class="flex-shrink-0 animate-[float_5s_ease-in-out_infinite]">
                            <ellipse cx="100" cy="105" rx="95" ry="18" fill="rgba(26,95,168,0.08)" />
                            <path d="M86 95L83 48H117L114 95H86Z" fill="white" stroke="#D1DCF0" stroke-width="1" />
                            <line x1="84" y1="78" x2="116" y2="78" stroke="rgba(26,95,168,0.18)" stroke-width="2" />
                            <line x1="83.5" y1="62" x2="116.5" y2="62" stroke="rgba(26,95,168,0.18)" stroke-width="2" />
                            <rect x="93" y="82" width="14" height="10" rx="2" fill="rgba(56,189,248,0.4)" />
                            <rect x="79" y="43" width="42" height="6" rx="2" fill="#E4EBF5" />
                            <rect x="83" y="28" width="34" height="16" rx="2" fill="white" stroke="#D1DCF0" stroke-width="1" />
                            <rect x="86" y="30" width="28" height="12" rx="1.5" fill="rgba(56,189,248,0.22)" />
                            <path d="M100 36L72 10L128 10Z" fill="rgba(255,225,60,0.18)" />
                            <circle cx="100" cy="34" r="5" fill="rgba(255,225,60,0.9)" />
                            <circle cx="100" cy="34" r="8" fill="rgba(255,225,60,0.18)" />
                            <path d="M85 28L100 20L115 28H85Z" fill="#E4EBF5" />
                            <path d="M15 100C30 94 45 106 60 100C75 94 90 106 105 100C120 94 135 106 150 100C165 94 180 106 195 100" stroke="rgba(26,95,168,0.3)" stroke-width="2.5" stroke-linecap="round" fill="none" />
                            <path d="M5 110C22 104 40 116 58 110C76 104 94 116 112 110C130 104 148 116 166 110C178 106 188 112 197 110" stroke="rgba(13,158,138,0.25)" stroke-width="2" stroke-linecap="round" fill="none" />
                        </svg>
                        <div>
                            <h2 class="mb-1 text-base font-bold text-navy">Selamat datang di Portal Pendamping DKP Jatim</h2>
                            <p class="text-sm leading-relaxed text-muted-foreground">
                                Portal ini membantu Anda mempersiapkan dokumen sebelum submit ke Google Form resmi dinas, serta memantau status pengajuan setelahnya.
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach (['Magang / PKL', 'WOPPS', 'KP'] as $tag)
                                    <span class="rounded-full bg-ocean/10 px-2.5 py-1 text-xs font-medium text-ocean">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <section id="persiapan">
                        <h2 class="mb-4 text-xs font-semibold uppercase tracking-widest text-muted-foreground">Pilih Jenis Layanan</h2>

                        <form method="POST" action="{{ route('peserta.application.store') }}">
                            @csrf
                            <fieldset>
                                <legend class="sr-only">Jenis layanan</legend>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    @foreach ($serviceOptions as $value => $option)
                                        @php $isWopps = $value === 'wopps'; @endphp
                                        <label class="group relative flex cursor-pointer flex-col overflow-hidden rounded-2xl border-[1.5px] border-[#E4EBF5] bg-white shadow-[0_2px_12px_rgba(26,95,168,0.06)] transition-all hover:-translate-y-0.5 has-[:checked]:border-{{ $isWopps ? 'teal' : 'ocean' }}">
                                            <input type="radio" name="service_type" value="{{ $value }}" class="peer sr-only" @checked(old('service_type') === $value)>

                                            <span class="h-1.5 w-full bg-gradient-to-r {{ $isWopps ? 'from-teal to-teal/50' : 'from-ocean to-ocean/50' }}"></span>

                                            <span class="flex flex-1 flex-col gap-4 p-6">
                                                <span class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl border-[1.5px] {{ $isWopps ? 'border-teal/25 bg-teal/10' : 'border-ocean/25 bg-ocean/10' }}">
                                                    <i data-lucide="{{ $isWopps ? 'search' : 'file-text' }}" class="h-6 w-6 {{ $isWopps ? 'text-teal' : 'text-ocean' }}" aria-hidden="true"></i>
                                                </span>

                                                <span class="flex-1">
                                                    <span class="mb-0.5 block text-base font-bold leading-snug text-navy">{{ $option['label'] }}</span>
                                                    <span class="mb-2 block text-xs font-semibold {{ $isWopps ? 'text-teal' : 'text-ocean' }}">{{ $option['tagline'] ?? '' }}</span>
                                                    <span class="block text-sm leading-relaxed text-muted-foreground">{{ $option['description'] }}</span>
                                                </span>

                                                <span class="flex w-full items-center justify-center gap-2 rounded-xl py-3 text-sm font-semibold text-white transition-transform group-hover:scale-[1.02] bg-gradient-to-br {{ $isWopps ? 'from-teal to-teal/80' : 'from-ocean to-ocean/80' }}">
                                                    <i data-lucide="arrow-right" class="h-4 w-4" aria-hidden="true"></i>
                                                    Mulai Pengajuan
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            @error('service_type')
                                <p class="mt-3 text-sm font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </form>
                    </section>

                    <div class="flex items-center gap-4 rounded-2xl border border-[#E4EBF5] bg-[#F8FAFF] p-5">
                        <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-ocean/10">
                            <i data-lucide="message-square" class="h-[22px] w-[22px] text-ocean" aria-hidden="true"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-navy">Belum yakin layanan mana yang sesuai?</p>
                            <p class="mt-0.5 text-xs text-muted-foreground">Tanya asisten chatbot kami untuk mendapat rekomendasi</p>
                        </div>
                        <a href="{{ route('chatbot') }}" class="flex flex-shrink-0 items-center gap-2 rounded-xl border-[1.5px] border-ocean px-4 py-2 text-sm font-semibold text-ocean hover:bg-ocean/5">
                            <i data-lucide="message-square" class="h-3.5 w-3.5" aria-hidden="true"></i>
                            Tanya Asisten
                        </a>
                    </div>

                @else
                    {{-- ═══ SUDAH ADA PENGAJUAN AKTIF ═══ --}}

                    <div id="ringkasan" class="overflow-hidden rounded-2xl bg-gradient-to-br from-navy to-ocean p-7 text-white shadow-xl shadow-navy/10 sm:p-8">
                        <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                            <div class="max-w-xl">
                                <p class="text-xs font-bold uppercase tracking-wider text-cyan/80">{{ $application->serviceLabel() }}</p>
                                <p class="mt-2 text-sm leading-relaxed text-white/80">Anda sedang berada pada tahap persiapan pengajuan. Lengkapi kebutuhan terlebih dahulu sebelum menuju Google Form resmi DKP.</p>
                            </div>
                            <div class="flex-shrink-0 rounded-2xl border border-white/15 bg-white/10 px-5 py-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-white/60">Status saat ini</p>
                                <p class="mt-1 text-lg font-bold">{{ $steps[0]['label'] ?? 'Persiapan' }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="mb-2 flex items-center justify-between text-xs font-semibold text-white/80">
                                <span>Tahap 1 dari {{ count($steps) }}</span>
                                <span>{{ $steps[0]['label'] ?? 'Persiapan' }}</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-white/15">
                                <div class="h-full rounded-full bg-cyan" style="width: {{ (1 / count($steps)) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>

                    @if ($application->service_type === \App\Models\ParticipantApplication::SERVICE_MAGANG_PKL)
                        @include('components.peserta.internship-workflow', [
                            'application' => $application,
                            'locations' => $internshipLocations,
                            'guestbookUrl' => $internshipGuestbookUrl,
                        ])
                    @else
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <a href="#persiapan" class="group rounded-2xl border border-[#E4EBF5] bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-ocean/10"><i data-lucide="file-check-2" class="h-5 w-5 text-ocean" aria-hidden="true"></i></span>
                                <span class="mt-4 block text-sm font-bold text-navy">Checklist Dokumen</span>
                                <span class="mt-1 block text-xs leading-relaxed text-muted-foreground">Pahami kebutuhan sebelum mengajukan.</span>
                            </a>
                            <div class="rounded-2xl border border-[#E4EBF5] bg-white p-5 opacity-70">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal/10"><i data-lucide="scan-search" class="h-5 w-5 text-teal" aria-hidden="true"></i></span>
                                <span class="mt-4 block text-sm font-bold text-navy">AI Document Checker</span>
                                <span class="mt-1 block text-xs leading-relaxed text-muted-foreground">Segera hadir pada tahap berikutnya.</span>
                            </div>
                            <div class="rounded-2xl border border-[#E4EBF5] bg-white p-5 opacity-70">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan/15"><i data-lucide="external-link" class="h-5 w-5 text-ocean" aria-hidden="true"></i></span>
                                <span class="mt-4 block text-sm font-bold text-navy">Google Form Resmi</span>
                                <span class="mt-1 block text-xs leading-relaxed text-muted-foreground">Terbuka setelah pemeriksaan dokumen tersedia.</span>
                            </div>
                            <a href="{{ route('chatbot') }}" class="group rounded-2xl border border-[#E4EBF5] bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal/10"><i data-lucide="message-circle" class="h-5 w-5 text-teal" aria-hidden="true"></i></span>
                                <span class="mt-4 block text-sm font-bold text-navy">Tanya Asisten</span>
                                <span class="mt-1 block text-xs leading-relaxed text-muted-foreground">Cari informasi dari dokumen resmi DKP.</span>
                            </a>
                        </div>

                        <div id="persiapan" class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
                            <article class="rounded-2xl border border-[#E4EBF5] bg-white p-6 shadow-sm sm:p-7">
                                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-teal">Persiapan pengajuan</p>
                                        <h2 class="mt-2 text-xl font-bold text-navy">Checklist dokumen awal</h2>
                                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Checklist ini membantu Anda menyiapkan dokumen. Status belum menjadi verifikasi resmi Dinas.</p>
                                    </div>
                                    <span class="w-fit flex-shrink-0 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">Belum ditinjau</span>
                                </div>
                                <div class="mt-6 divide-y divide-[#E4EBF5] rounded-2xl border border-[#E4EBF5]">
                                    @foreach ($application->preparationChecklist() as $item)
                                        <div class="flex gap-4 px-4 py-4 sm:px-5">
                                            <span class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-[#F4F7FB] text-muted-foreground"><i data-lucide="circle" class="h-4 w-4" aria-hidden="true"></i></span>
                                            <span>
                                                <span class="block text-sm font-bold text-navy">{{ $item['label'] }}</span>
                                                <span class="mt-0.5 block text-sm leading-relaxed text-muted-foreground">{{ $item['description'] }}</span>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </article>

                            <aside class="rounded-2xl border border-[#E4EBF5] bg-[#F8FAFF] p-6">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-ocean shadow-sm"><i data-lucide="circle-help" class="h-5 w-5" aria-hidden="true"></i></span>
                                <h2 class="mt-4 text-lg font-bold text-navy">Butuh bantuan?</h2>
                                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Gunakan Asisten untuk menanyakan persyaratan, alur, atau kontak layanan.</p>
                                <a href="{{ route('chatbot') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-ocean hover:text-navy">Buka Asisten <i data-lucide="arrow-up-right" class="h-4 w-4" aria-hidden="true"></i></a>
                            </aside>
                        </div>
                    @endif

                    <div id="progress" class="rounded-2xl border border-[#E4EBF5] bg-white p-6 shadow-sm sm:p-7">
                        <p class="text-xs font-bold uppercase tracking-wider text-teal">Monitoring pendamping</p>
                        <h2 class="mt-2 text-xl font-bold text-navy">Progress pengajuan</h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Status Dinas akan terlihat di sini ketika fitur monitoring administrasi telah diaktifkan oleh pengelola.</p>
                        <ol class="mt-6 grid gap-4 md:grid-cols-5">
                            @foreach ($steps as $index => $step)
                                <li class="relative rounded-2xl border p-4 {{ $index === 0 ? 'border-ocean bg-ocean/[0.04]' : 'border-[#E4EBF5] bg-[#F8FAFF]' }}">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $index === 0 ? 'bg-ocean text-white' : 'bg-[#E4EBF5] text-muted-foreground' }}">{{ $index + 1 }}</span>
                                    <span class="mt-3 block text-sm font-bold text-navy">{{ $step['label'] }}</span>
                                    <span class="mt-1 block text-xs leading-relaxed text-muted-foreground">{{ $step['description'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif

            </div>
        </main>
    </div>
@endsection