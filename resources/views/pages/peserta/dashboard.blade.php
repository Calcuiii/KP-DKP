@extends('layouts.app')

@section('title', 'Portal Peserta | SI-MELAYUR')

@section('hide_dev_nav', true)

@section('content')
    @php
        $participant = auth('peserta')->user();
        $steps = [
            ['label' => 'Persiapan', 'description' => 'Pilih layanan dan pahami kebutuhan dokumen.'],
            ['label' => 'Pemeriksaan dokumen', 'description' => 'Unggah dan periksa dokumen sebelum pengajuan.'],
            ['label' => 'Pengajuan resmi', 'description' => 'Isi Google Form resmi DKP.'],
            ['label' => 'Menunggu surat balasan', 'description' => 'Status resmi diperbarui oleh Dinas.'],
            ['label' => 'Pelaksanaan & penyelesaian', 'description' => 'Pantau informasi akhir kegiatan.'],
        ];
        $sidebarProgress = [
            ['label' => 'Pilih layanan', 'state' => $application ? 'done' : 'current'],
        ];

        if ($application) {
            if ($application->service_type === \App\Models\ParticipantApplication::SERVICE_MAGANG_PKL) {
                $letterApproved = $application->requestLetterApproved();
                $hasResponse = filled($application->response_letter_path) || filled($application->decision);
                $sidebarProgress = [
                    ['label' => 'Buku Tamu', 'state' => $application->guestbook_confirmed_at ? 'done' : 'current'],
                    ['label' => 'Info Kuota', 'state' => $application->guestbook_confirmed_at ? 'done' : 'upcoming'],
                    ['label' => 'Upload Surat', 'state' => $application->letter_submitted_at ? 'done' : ($application->guestbook_confirmed_at ? 'current' : 'upcoming')],
                    ['label' => 'Pemeriksaan', 'state' => $letterApproved ? 'done' : ($application->letter_submitted_at ? 'current' : 'upcoming')],
                    ['label' => 'Surat Balasan', 'state' => $hasResponse ? 'done' : ($letterApproved ? 'current' : 'upcoming')],
                    ['label' => 'Pelaksanaan', 'state' => $application->official_ended_at ? 'done' : (($hasResponse || $application->official_started_at) ? 'current' : 'upcoming')],
                ];
            } else {
                $sidebarProgress = [
                    ['label' => 'Persiapan', 'state' => 'current'],
                    ['label' => 'Dokumen', 'state' => 'upcoming'],
                    ['label' => 'Pengajuan', 'state' => 'upcoming'],
                    ['label' => 'Tindak lanjut', 'state' => 'upcoming'],
                ];
            }
        }

        $activeProgressIndex = collect($sidebarProgress)->search(fn ($item) => $item['state'] === 'current');
        $activeProgressIndex = $activeProgressIndex === false ? max(0, count($sidebarProgress) - 1) : $activeProgressIndex;
        $activeProgressLabel = $sidebarProgress[$activeProgressIndex]['label'];
        $activeStatusLabel = $application?->status === 'letter_revision_required' ? 'Revisi Surat Diperlukan' : $activeProgressLabel;
    @endphp

    <main class="participant-workspace min-h-screen bg-background font-sans text-navy">
        <header class="sticky top-0 z-40 border-b border-border bg-white/90 px-5 py-4 backdrop-blur-xl sm:px-8 lg:px-10">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-ocean to-teal text-white shadow-lg shadow-ocean/20">
                        <i data-lucide="compass" class="h-5 w-5" aria-hidden="true"></i>
                    </span>
                    <span>
                        <span class="block text-base font-extrabold tracking-tight">SI-MELAYUR</span>
                        <span class="block text-[11px] font-semibold text-muted-foreground">Portal pendamping peserta</span>
                    </span>
                </a>

                <div class="flex items-center gap-3">
                    <span class="hidden text-right sm:block">
                        <span class="block text-sm font-bold">{{ $participant->name }}</span>
                        <span class="block text-xs text-muted-foreground">Peserta terverifikasi</span>
                    </span>
                    <form method="POST" action="{{ route('peserta.logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-border px-3.5 py-2 text-xs font-bold text-muted-foreground transition hover:border-destructive/30 hover:text-destructive">
                            <i data-lucide="log-out" class="h-4 w-4" aria-hidden="true"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <nav class="border-b border-border bg-white px-5 py-3 lg:hidden" aria-label="Navigasi portal peserta">
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach ([
                    ['#kenali-si-molek', 'info', 'Informasi Portal'],
                    ['#ringkasan', 'home', 'Dashboard'],
                    [$application ? '#progress' : '#ringkasan', 'trending-up', 'Progress'],
                    ['#persiapan', 'file-check', 'Dokumen'],
                ] as [$href, $icon, $label])
                    <a href="{{ $href }}" data-participant-nav class="participant-mobile-nav inline-flex shrink-0 items-center gap-2 rounded-full border border-border bg-white px-4 py-2 text-xs font-bold text-muted-foreground">
                        <i data-lucide="{{ $icon }}" class="h-3.5 w-3.5"></i>{{ $label }}
                    </a>
                @endforeach
            </div>
        </nav>

        <div class="mx-auto flex max-w-[90rem] gap-6 px-5 py-7 sm:px-8 lg:px-10">
            <aside class="hidden w-64 shrink-0 lg:block">
                <nav class="participant-sidebar sticky top-24 overflow-hidden rounded-[2rem] bg-gradient-to-b from-[#173f78] to-navy p-3 text-white shadow-xl shadow-navy/15">
                    <div class="px-3 pb-4 pt-3">
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-200">Menu Portal</span>
                        <p class="mt-1 text-sm font-bold">Informasi dan ruang kerja</p>
                    </div>
                    @foreach ([
                        ['#kenali-si-molek', 'info', 'Informasi Portal'],
                        ['#ringkasan', 'home', 'Dashboard Saya'],
                        [$application ? '#progress' : '#ringkasan', 'trending-up', 'Status Pengajuan'],
                        ['#persiapan', 'file-check', 'Persiapan Dokumen'],
                    ] as [$href, $icon, $label])
                        <a href="{{ $href }}" data-participant-nav class="participant-sidebar-link flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold text-blue-100 transition">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10"><i data-lucide="{{ $icon }}" class="h-4 w-4"></i></span>
                            <span class="min-w-0 flex-1">{{ $label }}</span>
                        </a>
                    @endforeach

                    <div class="mx-3 my-4 border-t border-white/10"></div>
                    <div class="mx-2 rounded-2xl bg-white/[0.07] p-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-blue-200">Progress Anda</span>
                            <span class="rounded-full bg-white/10 px-2 py-1 text-[9px] font-bold text-blue-100">{{ collect($sidebarProgress)->where('state', 'done')->count() }}/{{ count($sidebarProgress) }}</span>
                        </div>
                        <ol class="mt-4">
                            @foreach ($sidebarProgress as $index => $progressItem)
                                <li class="relative flex min-h-12 gap-3 pb-3 last:min-h-0 last:pb-0">
                                    @if (! $loop->last)
                                        <span class="absolute left-[0.69rem] top-6 h-[calc(100%-0.35rem)] w-px {{ $progressItem['state'] === 'done' ? 'bg-teal' : 'bg-white/15' }}"></span>
                                    @endif
                                    <span class="relative z-10 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-[10px] font-extrabold {{ $progressItem['state'] === 'done' ? 'border-teal bg-teal text-white' : ($progressItem['state'] === 'current' ? 'border-white bg-white text-ocean ring-4 ring-white/10' : 'border-white/20 bg-navy/40 text-blue-300') }}">
                                        @if ($progressItem['state'] === 'done')
                                            <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </span>
                                    <span class="pt-0.5 text-[11px] font-bold leading-snug {{ $progressItem['state'] === 'upcoming' ? 'text-blue-300' : 'text-white' }}">{{ $progressItem['label'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                    <a href="{{ route('landing') }}" class="mx-2 mb-1 mt-2 flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-blue-200 transition hover:bg-white/10 hover:text-white"><i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Beranda SI-MELAYUR</a>
                </nav>
            </aside>

            <div class="participant-dashboard-content min-w-0 flex-1 space-y-6">
                @if (session('status'))
                    <div class="flex items-start gap-3 rounded-2xl border border-teal/25 bg-teal/10 px-5 py-4 text-sm font-medium text-teal">
                        <i data-lucide="circle-check" class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true"></i>
                        {{ session('status') }}
                    </div>
                @endif

                <section id="kenali-si-molek" class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-b from-[#176ac7] via-[#258ddd] to-[#74c8ec] px-6 py-8 text-white shadow-xl shadow-ocean/15 sm:px-10 sm:py-10">
                    <div class="pointer-events-none absolute left-8 top-10 h-6 w-20 rounded-full bg-white/30 blur-sm"></div>
                    <div class="pointer-events-none absolute right-12 top-16 h-8 w-28 rounded-full bg-white/20 blur-sm"></div>
                    <div class="pointer-events-none absolute -bottom-28 -right-20 h-72 w-72 rounded-full border-[3rem] border-white/[0.08]"></div>
                    <span class="relative z-20 mx-auto flex w-fit items-center gap-2 rounded-full border border-white/30 bg-white/15 px-4 py-2 text-[11px] font-bold text-white shadow-sm backdrop-blur">
                        <i data-lucide="fish" class="h-3.5 w-3.5" aria-hidden="true"></i>
                        <span>MULAI DARI SINI</span>
                    </span>

                    <div class="participant-intro-content">
                        <div class="participant-intro-content-inner">
                            <div class="relative mx-auto max-w-3xl pt-5 text-center">
                                <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Selamat datang di SI-MELAYUR, {{ $participant->name }}!</h1>
                                <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-blue-50 sm:text-base">
                                    SI-MELAYUR adalah <strong>Sistem Informasi Magang, Penelitian, dan Data Kelautan Jawa Timur</strong> milik Dinas Kelautan dan Perikanan Provinsi Jawa Timur. Nama ini terinspirasi dari ikan layur sebagai identitas bahari DKP Jawa Timur.
                                </p>
                            </div>

                    <div id="portal-pendampingan" class="relative mt-9 scroll-mt-28 overflow-hidden rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#edf8ff] via-white to-[#e7f8f5] px-5 py-7 text-navy shadow-2xl shadow-navy/15 sm:px-8 sm:py-9">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                            <div class="max-w-3xl">
                                <span class="inline-flex items-center gap-2 rounded-full bg-teal/10 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.18em] text-teal">
                                    <i data-lucide="map" class="h-3.5 w-3.5"></i> Panduan Portal Peserta
                                </span>
                                <h2 class="mt-4 text-2xl font-extrabold tracking-tight sm:text-3xl">Empat langkah menggunakan portal</h2>
                                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-muted-foreground">Pilih layanan, siapkan kebutuhan, gunakan panduan saat diperlukan, lalu pantau proses sampai layanan Anda selesai.</p>
                            </div>
                            <a href="#ringkasan" class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full bg-navy px-5 py-3 text-xs font-bold text-white shadow-lg shadow-navy/15 transition hover:-translate-y-0.5 hover:bg-ocean">Mulai persiapan <i data-lucide="arrow-down" class="h-3.5 w-3.5"></i></a>
                        </div>

                        <ol id="cara-penggunaan" class="mt-7 grid scroll-mt-28 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ([
                                ['target', 'Pilih layanan', 'Tentukan layanan Magang/PKL atau WOPPS yang ingin Anda gunakan.'],
                                ['clipboard-check', 'Siapkan kebutuhan', 'Baca checklist dan lengkapi dokumen sesuai layanan pilihan.'],
                                ['book-open', 'Gunakan panduan', 'Cari informasi resmi melalui panduan atau Asisten SI-MELAYUR.'],
                                ['trending-up', 'Pantau proses', 'Ikuti status dan tindak lanjut yang tersedia hingga layanan selesai.'],
                            ] as $index => [$icon, $title, $description])
                                <li class="relative rounded-2xl border border-ocean/10 bg-white/90 p-4 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-ocean to-teal text-white"><i data-lucide="{{ $icon }}" class="h-4 w-4"></i></span>
                                        <span class="text-[10px] font-extrabold uppercase tracking-[0.15em] text-ocean/60">Langkah {{ $index + 1 }}</span>
                                    </div>
                                    <h3 class="mt-4 text-sm font-extrabold">{{ $title }}</h3>
                                    <p class="mt-1 text-xs leading-relaxed text-muted-foreground">{{ $description }}</p>
                                    @if ($index < 3)
                                        <span class="absolute -right-2 top-1/2 z-10 hidden h-5 w-5 -translate-y-1/2 items-center justify-center rounded-full bg-white text-ocean shadow-sm xl:flex"><i data-lucide="chevron-right" class="h-3 w-3"></i></span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                        </div>
                    </div>
                </section>

                @if (! $application)
                    <section id="ringkasan" class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy via-[#123d72] to-ocean p-7 text-white shadow-xl shadow-navy/10 sm:p-10">
                        <div class="max-w-2xl">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-teal-200">Portal Peserta SI-MELAYUR</p>
                            <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Halo, {{ $participant->name }} 👋</h1>
                            <p class="mt-3 max-w-xl text-sm leading-relaxed text-blue-100 sm:text-base">Mulailah dengan memilih layanan yang ingin Anda persiapkan. SI-MELAYUR membantu menata kebutuhan sebelum Anda mengirim pengajuan melalui Google Form resmi DKP.</p>
                        </div>
                    </section>

                    <section id="persiapan" class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
                        <div class="max-w-2xl">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Langkah pertama</p>
                            <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-navy">Pilih layanan yang akan dipersiapkan</h2>
                            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Pilihan ini menentukan checklist awal Anda. Ini belum merupakan pengajuan resmi ke Dinas.</p>
                        </div>

                        <form method="POST" action="{{ route('peserta.application.store') }}" class="mt-6">
                            @csrf
                            <fieldset>
                                <legend class="sr-only">Jenis layanan</legend>
                                <div class="grid gap-4 md:grid-cols-2">
                                    @foreach ($serviceOptions as $value => $option)
                                        <label class="group cursor-pointer rounded-3xl border border-border bg-light/60 p-5 transition hover:border-ocean/40 hover:bg-white has-[:checked]:border-ocean has-[:checked]:bg-ocean/[0.04]">
                                            <input type="radio" name="service_type" value="{{ $value }}" class="peer sr-only" @checked(old('service_type') === $value)>
                                            <span class="flex items-start justify-between gap-4">
                                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-ocean/10 text-ocean group-has-[:checked]:bg-ocean group-has-[:checked]:text-white">
                                                    <i data-lucide="{{ $value === 'wopps' ? 'microscope' : 'graduation-cap' }}" class="h-5 w-5" aria-hidden="true"></i>
                                                </span>
                                                <span class="flex h-5 w-5 items-center justify-center rounded-full border-2 border-muted-foreground/30 peer-checked:border-ocean peer-checked:bg-ocean">
                                                    <i data-lucide="check" class="hidden h-3 w-3 text-white peer-checked:block" aria-hidden="true"></i>
                                                </span>
                                            </span>
                                            <span class="mt-5 block text-base font-extrabold text-navy">{{ $option['label'] }}</span>
                                            <span class="mt-1 block text-sm leading-relaxed text-muted-foreground">{{ $option['description'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            @error('service_type')
                                <p class="mt-3 text-sm font-medium text-destructive">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="mt-6 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-ocean to-teal px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-ocean/20 transition hover:brightness-110">
                                Buat Persiapan Pengajuan
                                <i data-lucide="arrow-right" class="h-4 w-4" aria-hidden="true"></i>
                            </button>
                        </form>
                    </section>
                @else
                    <section id="ringkasan" class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy via-[#123d72] to-ocean p-7 text-white shadow-xl shadow-navy/10 sm:p-10">
                        <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                            <div class="max-w-2xl">
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-teal-200">{{ $application->serviceLabel() }}</p>
                                <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Halo, {{ $participant->name }} 👋</h1>
                                <p class="mt-3 text-sm leading-relaxed text-blue-100 sm:text-base">Tahap aktif Anda adalah <strong class="text-white">{{ $activeStatusLabel }}</strong>. Ikuti langkah yang ditandai untuk melanjutkan proses layanan.</p>
                            </div>
                            <div class="rounded-3xl border border-white/15 bg-white/10 px-5 py-4 backdrop-blur-sm">
                                <p class="text-xs font-bold uppercase tracking-wider text-blue-200">Status saat ini</p>
                                <p class="mt-1 text-lg font-extrabold">{{ $activeStatusLabel }}</p>
                            </div>
                        </div>
                        @if ($application->service_type === \App\Models\ParticipantApplication::SERVICE_MAGANG_PKL)
                            <div id="progress" class="scroll-mt-28 mt-8 border-t border-white/10 pt-6">
                                <div class="flex items-center justify-between text-xs font-bold text-blue-100">
                                    <span>Alur Magang / PKL / Kerja Praktik</span>
                                    <span>Tahap {{ $activeProgressIndex + 1 }} dari {{ count($sidebarProgress) }}</span>
                                </div>
                                <ol class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                                    @foreach ($sidebarProgress as $index => $progressItem)
                                        <li class="rounded-2xl border p-3.5 {{ $progressItem['state'] === 'done' ? 'border-teal/50 bg-teal/15' : ($progressItem['state'] === 'current' ? 'border-white/60 bg-white/15 ring-2 ring-white/10' : 'border-white/10 bg-white/[0.04]') }}">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-extrabold {{ $progressItem['state'] === 'done' ? 'bg-teal text-white' : ($progressItem['state'] === 'current' ? 'bg-white text-ocean' : 'bg-white/10 text-blue-300') }}">
                                                @if ($progressItem['state'] === 'done')<i data-lucide="check" class="h-3.5 w-3.5"></i>@else{{ $index + 1 }}@endif
                                            </span>
                                            <span class="mt-3 block text-xs font-bold {{ $progressItem['state'] === 'upcoming' ? 'text-blue-300' : 'text-white' }}">{{ $progressItem['label'] }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        @else
                            <div id="progress" class="scroll-mt-28 mt-8">
                                <div class="flex items-center justify-between text-xs font-bold text-blue-100"><span>Tahap {{ $activeProgressIndex + 1 }} dari {{ count($sidebarProgress) }}</span><span>{{ $activeProgressLabel }}</span></div>
                                <div class="mt-2 h-3 overflow-hidden rounded-full bg-white/15"><div class="h-full rounded-full bg-teal" style="width: {{ (($activeProgressIndex + 1) / count($sidebarProgress)) * 100 }}%"></div></div>
                            </div>
                        @endif
                    </section>

                    @if ($application->service_type === \App\Models\ParticipantApplication::SERVICE_MAGANG_PKL)
                        @include('components.peserta.internship-workflow', [
                            'application' => $application,
                            'locations' => $internshipLocations,
                            'guestbookUrl' => $internshipGuestbookUrl,
                        ])
                    @else
                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <a href="#persiapan" class="group rounded-3xl border border-border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-ocean/10 text-ocean"><i data-lucide="file-check-2" class="h-5 w-5" aria-hidden="true"></i></span>
                            <span class="mt-5 block text-sm font-extrabold text-navy">Checklist Dokumen</span>
                            <span class="mt-1 block text-xs leading-relaxed text-muted-foreground">Pahami kebutuhan sebelum mengajukan.</span>
                        </a>
                        <div class="rounded-3xl border border-border bg-white p-5 opacity-70 shadow-sm">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal/10 text-teal"><i data-lucide="scan-search" class="h-5 w-5" aria-hidden="true"></i></span>
                            <span class="mt-5 block text-sm font-extrabold text-navy">AI Document Checker</span>
                            <span class="mt-1 block text-xs leading-relaxed text-muted-foreground">Segera hadir pada tahap berikutnya.</span>
                        </div>
                        <div class="rounded-3xl border border-border bg-white p-5 opacity-70 shadow-sm">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan/15 text-ocean"><i data-lucide="external-link" class="h-5 w-5" aria-hidden="true"></i></span>
                            <span class="mt-5 block text-sm font-extrabold text-navy">Google Form Resmi</span>
                            <span class="mt-1 block text-xs leading-relaxed text-muted-foreground">Terbuka setelah pemeriksaan dokumen tersedia.</span>
                        </div>
                        <a href="{{ route('chatbot') }}" class="group rounded-3xl border border-border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal/10 text-teal"><i data-lucide="message-circle" class="h-5 w-5" aria-hidden="true"></i></span>
                            <span class="mt-5 block text-sm font-extrabold text-navy">Tanya Asisten</span>
                            <span class="mt-1 block text-xs leading-relaxed text-muted-foreground">Cari informasi dari dokumen resmi DKP.</span>
                        </a>
                    </section>

                    <section id="persiapan" class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
                        <article class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
                            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Persiapan pengajuan</p>
                                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight">Checklist dokumen awal</h2>
                                    <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Checklist ini membantu Anda menyiapkan dokumen. Status belum menjadi verifikasi resmi Dinas.</p>
                                </div>
                                <span class="inline-flex w-fit rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">Belum ditinjau</span>
                            </div>
                            <div class="mt-6 divide-y divide-border rounded-2xl border border-border">
                                @foreach ($application->preparationChecklist() as $item)
                                    <div class="flex gap-4 px-4 py-4 sm:px-5">
                                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground"><i data-lucide="circle" class="h-4 w-4" aria-hidden="true"></i></span>
                                        <span>
                                            <span class="block text-sm font-bold text-navy">{{ $item['label'] }}</span>
                                            <span class="mt-0.5 block text-sm leading-relaxed text-muted-foreground">{{ $item['description'] }}</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-5 text-xs leading-relaxed text-muted-foreground">Fitur unggah dan pemeriksaan dokumen akan ditambahkan setelah fondasi persiapan ini selesai.</p>
                        </article>

                        <aside class="rounded-[2rem] border border-border bg-light/60 p-6 sm:p-7">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-ocean shadow-sm"><i data-lucide="circle-help" class="h-5 w-5" aria-hidden="true"></i></span>
                            <h2 class="mt-5 text-xl font-extrabold">Butuh bantuan?</h2>
                            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Gunakan Asisten SI-MELAYUR untuk menanyakan persyaratan, alur, atau kontak layanan.</p>
                            <a href="{{ route('chatbot') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-ocean transition hover:text-navy">Buka Asisten SI-MELAYUR <i data-lucide="arrow-up-right" class="h-4 w-4" aria-hidden="true"></i></a>
                        </aside>
                    </section>

                    <section id="progress" class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Monitoring pendamping</p>
                        <h2 class="mt-2 text-2xl font-extrabold tracking-tight">Progress pengajuan</h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Status Dinas akan terlihat di sini ketika fitur monitoring administrasi telah diaktifkan oleh pengelola.</p>
                        <ol class="mt-7 grid gap-4 md:grid-cols-5">
                            @foreach ($steps as $index => $step)
                                <li class="relative rounded-2xl border p-4 {{ $index === 0 ? 'border-ocean bg-ocean/[0.04]' : 'border-border bg-light/40' }}">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-extrabold {{ $index === 0 ? 'bg-ocean text-white' : 'bg-muted text-muted-foreground' }}">{{ $index + 1 }}</span>
                                    <span class="mt-3 block text-sm font-extrabold text-navy">{{ $step['label'] }}</span>
                                    <span class="mt-1 block text-xs leading-relaxed text-muted-foreground">{{ $step['description'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                    @endif
                @endif
            </div>
        </div>
    </main>
@endsection
