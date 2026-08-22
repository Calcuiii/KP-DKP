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
                    ['#kenali-si-molek', 'compass', 'Pengenalan'],
                    ['#portal-pendampingan', 'layers', 'Portal'],
                    ['#cara-penggunaan', 'check-circle', 'Cara pakai'],
                    ['#ringkasan', 'home', 'Dashboard'],
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
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-200">Panduan Awal</span>
                        <p class="mt-1 text-sm font-bold">Mulai dengan memahami portal</p>
                    </div>
                    @foreach ([
                        ['#kenali-si-molek', 'compass', 'Kenali SI-MELAYUR', '01'],
                        ['#portal-pendampingan', 'layers', 'Portal Pendampingan', '02'],
                        ['#cara-penggunaan', 'check-circle', 'Cara Penggunaan', '03'],
                    ] as [$href, $icon, $label, $number])
                        <a href="{{ $href }}" data-participant-nav class="participant-sidebar-link flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold text-blue-100 transition">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10"><i data-lucide="{{ $icon }}" class="h-4 w-4"></i></span>
                            <span class="min-w-0 flex-1">{{ $label }}</span><span class="text-[10px] text-blue-300">{{ $number }}</span>
                        </a>
                    @endforeach

                    <div class="mx-3 my-4 border-t border-white/10"></div>
                    <div class="px-3 pb-2"><span class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-200">Ruang Kerja</span></div>
                    @foreach ([
                        ['#ringkasan', 'home', 'Dashboard Saya'],
                        ['#persiapan', 'file-check', 'Persiapan Dokumen'],
                        ['#progress', 'trending-up', 'Status Pengajuan'],
                    ] as [$href, $icon, $label])
                        <a href="{{ $href }}" data-participant-nav class="participant-sidebar-link flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold text-blue-100 transition">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/10"><i data-lucide="{{ $icon }}" class="h-4 w-4"></i></span>{{ $label }}
                        </a>
                    @endforeach

                    <div class="m-2 mt-5 rounded-2xl bg-white p-4 text-navy shadow-lg">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal/10 text-teal"><i data-lucide="message-square" class="h-4 w-4"></i></span>
                        <p class="mt-3 text-xs font-extrabold">Masih bingung?</p>
                        <p class="mt-1 text-[11px] leading-relaxed text-muted-foreground">Asisten SI-MELAYUR siap membantu mencari informasi resmi.</p>
                        <a href="{{ route('chatbot') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-ocean">Tanya Asisten <i data-lucide="arrow-right" class="h-3 w-3"></i></a>
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

                <section id="kenali-si-molek" class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-b from-[#176ac7] via-[#258ddd] to-[#74c8ec] px-6 pb-0 pt-8 text-white shadow-xl shadow-ocean/15 sm:px-10 sm:pt-10">
                    <div class="pointer-events-none absolute left-8 top-10 h-6 w-20 rounded-full bg-white/30 blur-sm"></div>
                    <div class="pointer-events-none absolute right-12 top-16 h-8 w-28 rounded-full bg-white/20 blur-sm"></div>
                    <div class="relative mx-auto max-w-3xl text-center">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-3 py-1.5 text-[11px] font-bold backdrop-blur">
                            <i data-lucide="fish" class="h-3.5 w-3.5" aria-hidden="true"></i> MULAI DARI SINI
                        </span>
                        <h1 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Selamat datang di SI-MELAYUR, {{ $participant->name }}!</h1>
                        <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-blue-50 sm:text-base">
                            SI-MELAYUR adalah <strong>Sistem Informasi Magang, Penelitian, dan Data Kelautan Jawa Timur</strong> milik Dinas Kelautan dan Perikanan Provinsi Jawa Timur. Nama ini terinspirasi dari ikan layur sebagai identitas bahari DKP Jawa Timur.
                        </p>
                    </div>

                    <div class="relative mx-auto mt-8 max-w-3xl translate-y-6 rounded-t-[1.75rem] border border-white/60 bg-white p-4 text-navy shadow-2xl sm:p-5">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-navy p-4 text-white"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15"><i data-lucide="file-check" class="h-4 w-4"></i></span><p class="mt-4 text-sm font-bold">Persiapan Terarah</p><p class="mt-1 text-xs leading-relaxed text-blue-100">Menata kebutuhan sebelum pengajuan resmi.</p></div>
                            <div class="rounded-2xl bg-light p-4"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal/10 text-teal"><i data-lucide="book-open" class="h-4 w-4"></i></span><p class="mt-4 text-sm font-bold">Panduan Resmi</p><p class="mt-1 text-xs leading-relaxed text-muted-foreground">Informasi layanan lebih mudah dipahami.</p></div>
                            <div class="rounded-2xl bg-light p-4"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean/10 text-ocean"><i data-lucide="message-square" class="h-4 w-4"></i></span><p class="mt-4 text-sm font-bold">Bantuan Informasi</p><p class="mt-1 text-xs leading-relaxed text-muted-foreground">Asisten menjawab dari dokumen yang tersedia.</p></div>
                        </div>
                    </div>
                    <div class="h-10"></div>
                </section>

                <section id="portal-pendampingan" class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
                    <div class="grid gap-8 lg:grid-cols-[.8fr_1.2fr] lg:items-center">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-[0.2em] text-teal">Portal Pendampingan Peserta</span>
                            <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">Apa fungsi portal ini?</h2>
                            <p class="mt-3 text-sm leading-relaxed text-muted-foreground">Portal Pendampingan adalah ruang kerja pribadi Anda di dalam SI-MELAYUR. Di sini, Anda dipandu untuk memahami tahapan dan mempersiapkan kebutuhan layanan sebelum menuju kanal pengajuan resmi DKP.</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach ([
                                ['01', 'Pilih layanan', 'Tentukan Magang/PKL atau WOPPS.'],
                                ['02', 'Siapkan kebutuhan', 'Pelajari checklist dan dokumen awal.'],
                                ['03', 'Pantau tahapan', 'Lihat posisi proses layanan Anda.'],
                            ] as [$number, $title, $description])
                                <div class="rounded-2xl border border-border bg-background p-4">
                                    <span class="text-xs font-extrabold text-ocean">{{ $number }}</span>
                                    <h3 class="mt-5 text-sm font-extrabold">{{ $title }}</h3>
                                    <p class="mt-1 text-xs leading-relaxed text-muted-foreground">{{ $description }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="cara-penggunaan" class="rounded-[2rem] bg-navy p-6 text-white shadow-sm sm:p-8">
                    <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-lg">
                            <span class="text-xs font-bold uppercase tracking-[0.2em] text-teal-200">Cara Penggunaan</span>
                            <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">Empat langkah untuk memulai</h2>
                            <p class="mt-3 text-sm leading-relaxed text-blue-100">Setelah memahami alurnya, lanjutkan ke bagian dashboard di bawah untuk memulai persiapan.</p>
                        </div>
                        <a href="#ringkasan" class="inline-flex w-fit items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-navy transition hover:bg-blue-50">Mulai sekarang <i data-lucide="arrow-down" class="h-4 w-4"></i></a>
                    </div>
                    <ol class="mt-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ([
                            ['Buat persiapan', 'Pilih jenis layanan yang dibutuhkan.'],
                            ['Baca checklist', 'Pahami data dan dokumen yang perlu disiapkan.'],
                            ['Gunakan panduan', 'Buka infografis atau Asisten jika membutuhkan informasi.'],
                            ['Lanjutkan proses', 'Ikuti tahapan sampai kanal pengajuan resmi tersedia.'],
                        ] as $index => [$title, $description])
                            <li class="rounded-2xl border border-white/10 bg-white/[0.07] p-4">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-teal text-xs font-extrabold">{{ $index + 1 }}</span>
                                <h3 class="mt-4 text-sm font-bold">{{ $title }}</h3>
                                <p class="mt-1 text-xs leading-relaxed text-blue-100">{{ $description }}</p>
                            </li>
                        @endforeach
                    </ol>
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
                                <p class="mt-3 text-sm leading-relaxed text-blue-100 sm:text-base">Anda sedang berada pada tahap persiapan pengajuan. Lengkapi kebutuhan terlebih dahulu sebelum menuju Google Form resmi DKP.</p>
                            </div>
                            <div class="rounded-3xl border border-white/15 bg-white/10 px-5 py-4 backdrop-blur-sm">
                                <p class="text-xs font-bold uppercase tracking-wider text-blue-200">Status saat ini</p>
                                <p class="mt-1 text-lg font-extrabold">Persiapan Pengajuan</p>
                            </div>
                        </div>
                        <div class="mt-8">
                            <div class="flex items-center justify-between text-xs font-bold text-blue-100"><span>Tahap 1 dari {{ count($steps) }}</span><span>Persiapan</span></div>
                            <div class="mt-2 h-3 overflow-hidden rounded-full bg-white/15"><div class="h-full w-1/5 rounded-full bg-teal"></div></div>
                        </div>
                    </section>

                    @if ($application->service_type === \App\Models\ParticipantApplication::SERVICE_MAGANG_PKL)
                        @include('components.peserta.internship-workflow', [
                            'application' => $application,
                            'locations' => $internshipLocations,
                            'guestbookUrl' => $internshipGuestbookUrl,
                        ])
                    @elseif ($application->service_type === \App\Models\ParticipantApplication::SERVICE_WOPPS)
                        @include('components.peserta.wopps-workflow', [
                            'application' => $application,
                        ])
                    @endif

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
            </div>
        </div>
    </main>
@endsection
