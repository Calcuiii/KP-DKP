@extends('layouts.app')

@section('title', 'Portal Peserta | Si-Molek')

@section('hide_dev_nav', true)

@section('content')
    @php
        $participant = auth('peserta')->user();

        $application = $application ?? null;

        $steps = [
            [
                'label' => 'Persiapan',
                'description' => 'Pilih layanan dan pahami kebutuhan dokumen.'
            ],
            [
                'label' => 'Pemeriksaan dokumen',
                'description' => 'Unggah dan periksa dokumen sebelum pengajuan.'
            ],
            [
                'label' => 'Pengajuan resmi',
                'description' => 'Isi Google Form resmi DKP.'
            ],
            [
                'label' => 'Menunggu surat balasan',
                'description' => 'Status resmi diperbarui oleh Dinas.'
            ],
            [
                'label' => 'Pelaksanaan & penyelesaian',
                'description' => 'Pantau informasi akhir kegiatan.'
            ],
        ];
    @endphp

    <main class="min-h-screen bg-[#fbf7eb] font-sans text-navy">

        {{-- =====================================================
            HEADER
        ====================================================== --}}
        <header class="sticky top-0 z-40 h-[58px] border-b border-[#eee7d7] bg-[#fffdf3]/95 px-5 backdrop-blur-xl sm:px-8">
            <div class="mx-auto flex h-full max-w-[1000px] items-center justify-between">

                {{-- Logo --}}
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-ocean to-teal text-white shadow-md shadow-ocean/20">
                        <i data-lucide="compass" class="h-[17px] w-[17px]" aria-hidden="true"></i>
                    </span>

                    <span>
                        <span class="block text-[13px] font-extrabold leading-none tracking-tight">
                            Si-Molek
                        </span>
                        <span class="mt-0.5 block text-[9px] font-semibold leading-none text-muted-foreground">
                            Portal pendamping peserta
                        </span>
                    </span>
                </a>

                {{-- User --}}
                <div class="flex items-center gap-3">

                    <div class="hidden text-right sm:block">
                        <span class="block text-[11px] font-extrabold leading-tight">
                            {{ $participant->name }}
                        </span>

                        <span class="block text-[9px] leading-tight text-muted-foreground">
                            Peserta terverifikasi
                        </span>
                    </div>

                    <form method="POST" action="{{ route('peserta.logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#e9e2d2] bg-white/60 px-3 py-1.5 text-[10px] font-bold text-muted-foreground transition hover:border-destructive/30 hover:text-destructive"
                        >
                            <i data-lucide="log-out" class="h-3.5 w-3.5" aria-hidden="true"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>


        {{-- =====================================================
            MOBILE NAVIGATION
        ====================================================== --}}
        <nav
            class="border-b border-[#eee7d7] bg-[#fffdf3] px-5 py-3 lg:hidden"
            aria-label="Navigasi portal peserta"
        >
            <div class="flex gap-2 overflow-x-auto pb-1">

                @foreach ([
                    ['#kenali-si-molek', 'compass', 'Pengenalan'],
                    ['#portal-pendampingan', 'layers', 'Portal'],
                    ['#cara-penggunaan', 'check-circle', 'Cara pakai'],
                    ['#ringkasan', 'home', 'Dashboard'],
                    ['#persiapan', 'file-check', 'Dokumen'],
                ] as [$href, $icon, $label])

                    <a
                        href="{{ $href }}"
                        data-participant-nav
                        class="inline-flex shrink-0 items-center gap-2 rounded-full border border-border bg-white px-4 py-2 text-xs font-bold text-muted-foreground"
                    >
                        <i data-lucide="{{ $icon }}" class="h-3.5 w-3.5"></i>
                        {{ $label }}
                    </a>

                @endforeach

            </div>
        </nav>


        {{-- =====================================================
            MAIN CONTENT
        ====================================================== --}}
        <div class="mx-auto flex w-full max-w-[1280px] gap-5 px-5 py-6 sm:px-8 lg:px-10">

            {{-- =================================================
                SIDEBAR
            ================================================== --}}
            <aside class="hidden w-[220px] shrink-0 lg:block">

                <nav
                    class="sticky top-[74px] min-h-[calc(100vh-95px)] overflow-hidden rounded-[24px] bg-gradient-to-b from-[#293f83] to-[#182653] p-3 text-white shadow-lg shadow-navy/10"
                >

                    {{-- Panduan Awal --}}
                    <div class="px-2.5 pb-2.5 pt-2.5">

                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-200">
                            Panduan Awal
                        </span>

                        <p class="mt-1 text-sm font-extrabold leading-[1.35]">
                            Mulai dengan memahami portal
                        </p>

                    </div>


                    {{-- Menu Panduan --}}
                    @foreach ([
                        ['#kenali-si-molek', 'compass', 'Kenali Si-Molek', '01'],
                        ['#portal-pendampingan', 'layers', 'Portal Pendampingan', '02'],
                        ['#cara-penggunaan', 'check-circle', 'Cara Penggunaan', '03'],
                    ] as [$href, $icon, $label, $number])

                        <a
                            href="{{ $href }}"
                            data-participant-nav
                            class="participant-sidebar-link group flex items-center gap-2 rounded-[14px] px-2 py-2.5 text-xs font-semibold transition
                            {{ $href === '#kenali-si-molek'
                                ? 'bg-white text-navy shadow-sm'
                                : 'text-blue-100 hover:bg-white/10' }}"
                        >

                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[11px]
                                {{ $href === '#kenali-si-molek'
                                    ? 'bg-teal/10 text-teal'
                                    : 'bg-white/10 text-blue-100' }}"
                            >
                                <i
                                    data-lucide="{{ $icon }}"
                                    class="h-3.5 w-3.5"
                                    aria-hidden="true"
                                ></i>
                            </span>

                            <span class="min-w-0 flex-1 leading-[1.25]">
                                {{ $label }}
                            </span>

                            <span
                                class="text-[7px]
                                {{ $href === '#kenali-si-molek'
                                    ? 'text-ocean'
                                    : 'text-blue-300' }}"
                            >
                                {{ $number }}
                            </span>

                        </a>

                    @endforeach


                    {{-- Divider --}}
                    <div class="mx-2.5 my-3 border-t border-white/10"></div>


                    {{-- Ruang Kerja --}}
                    <div class="px-2.5 pb-1.5">

                        <span class="text-[8px] font-bold uppercase tracking-[0.2em] text-blue-200">
                            Ruang Kerja
                        </span>

                    </div>


                    @foreach ([
                        ['#ringkasan', 'home', 'Dashboard Saya'],
                        ['#persiapan', 'file-check', 'Persiapan Dokumen'],
                        ['#progress', 'trending-up', 'Status Pengajuan'],
                    ] as [$href, $icon, $label])

                        <a
                            href="{{ $href }}"
                            data-participant-nav
                            class="flex items-center gap-2 rounded-[14px] px-2 py-2.5 text-[10px] font-semibold text-blue-100 transition hover:bg-white/10"
                        >

                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-[10px] bg-white/10">
                                <i
                                    data-lucide="{{ $icon }}"
                                    class="h-3.5 w-3.5"
                                    aria-hidden="true"
                                ></i>
                            </span>

                            <span class="leading-[1.25]">
                                {{ $label }}
                            </span>

                        </a>

                    @endforeach


                    {{-- Bantuan --}}
                    <div class="mx-1.5 mt-4 rounded-[16px] bg-white p-3 text-navy shadow-lg">

                        <span class="flex h-8 w-8 items-center justify-center rounded-[10px] bg-teal/10 text-teal">
                            <i
                                data-lucide="message-square"
                                class="h-3.5 w-3.5"
                                aria-hidden="true"
                            ></i>
                        </span>

                        <p class="mt-2.5 text-[10px] font-extrabold">
                            Masih bingung?
                        </p>

                        <p class="mt-1 text-[9px] leading-[1.45] text-muted-foreground">
                            Asisten Si-Molek siap membantu mencari informasi resmi.
                        </p>

                        <a
                            href="{{ route('chatbot') }}"
                            class="mt-2.5 inline-flex items-center gap-1 text-[9px] font-bold text-ocean"
                        >
                            Tanya Asisten

                            <i
                                data-lucide="arrow-right"
                                class="h-3 w-3"
                                aria-hidden="true"
                            ></i>
                        </a>

                    </div>


                    {{-- Beranda --}}
                    <a
                        href="{{ route('landing') }}"
                        class="mx-1.5 mt-2 flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 text-[9px] font-semibold text-blue-200 transition hover:bg-white/10 hover:text-white"
                    >
                        <i
                            data-lucide="arrow-left"
                            class="h-3 w-3"
                            aria-hidden="true"
                        ></i>

                        Beranda Si-Molek
                    </a>

                </nav>

            </aside>


            {{-- =================================================
                DASHBOARD CONTENT
            ================================================== --}}
            <div class="min-w-0 flex-1 space-y-4">

                {{-- Session Status --}}
                @if (session('status'))

                    <div class="flex items-start gap-3 rounded-2xl border border-teal/25 bg-teal/10 px-5 py-4 text-sm font-medium text-teal">

                        <i
                            data-lucide="circle-check"
                            class="mt-0.5 h-5 w-5 shrink-0"
                            aria-hidden="true"
                        ></i>

                        {{ session('status') }}

                    </div>

                @endif


                {{-- =================================================
                    HERO
                ================================================== --}}
                <section
                    id="kenali-si-molek"
                    class="relative isolate h-[340px] overflow-hidden rounded-[24px] bg-gradient-to-b from-[#3857bf] via-[#447dd1] to-[#76c9e8] px-6 pb-0 pt-7 text-white shadow-lg shadow-ocean/10 sm:px-8"
                >

                    {{-- Decorative Blur --}}
                    <div class="pointer-events-none absolute left-7 top-7 h-5 w-16 rounded-full bg-white/20 blur-sm"></div>

                    <div class="pointer-events-none absolute right-10 top-12 h-7 w-24 rounded-full bg-white/15 blur-sm"></div>

                    <div class="pointer-events-none absolute -bottom-16 -right-10 h-36 w-36 rounded-full border-[18px] border-white/10"></div>


                    {{-- Hero Heading --}}
                    <div class="relative mx-auto max-w-[650px] text-center">

                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/15 px-3 py-1 text-[8px] font-bold backdrop-blur">
                            <i
                                data-lucide="compass"
                                class="h-3 w-3"
                                aria-hidden="true"
                            ></i>

                            MULAI DARI SINI
                        </span>

                        <h1 class="mt-4 text-[25px] font-extrabold leading-tight tracking-tight sm:text-[26px]">
                            Selamat datang di Si-Molek, {{ $participant->name }}!
                        </h1>

                        <p class="mx-auto mt-2.5 max-w-[600px] text-[11px] font-medium leading-[1.7] text-blue-50 sm:text-xs">
                            Si-Molek adalah
                            <strong>
                                Sistem Informasi Manajemen Otomatisasi Layanan Kerja Praktik,
                                Magang, PKL, dan WOPPS
                            </strong>
                            milik Dinas Kelautan dan Perikanan Provinsi Jawa Timur.
                        </p>

                    </div>


                    {{-- Feature Cards --}}
                    <div
                        class="relative mx-auto mt-6 max-w-[570px] translate-y-6 rounded-[20px] border border-white/60 bg-white p-2.5 text-navy shadow-xl"
                    >

                        <div class="grid gap-2 sm:grid-cols-3">

                            {{-- Card 1 --}}
                            <div class="min-h-[108px] rounded-[15px] bg-[#17203f] p-3.5 text-white">

                                <span class="flex h-8 w-8 items-center justify-center rounded-[10px] bg-white/10">
                                    <i
                                        data-lucide="file-check"
                                        class="h-3.5 w-3.5"
                                        aria-hidden="true"
                                    ></i>
                                </span>

                                <p class="mt-3 text-[10px] font-extrabold">
                                    Persiapan Terarah
                                </p>

                                <p class="mt-1 text-[8px] leading-[1.45] text-blue-100">
                                    Menata kebutuhan sebelum pengajuan resmi.
                                </p>

                            </div>


                            {{-- Card 2 --}}
                            <div class="min-h-[108px] rounded-[15px] bg-[#fff8ed] p-3.5">

                                <span class="flex h-8 w-8 items-center justify-center rounded-[10px] bg-teal/10 text-teal">
                                    <i
                                        data-lucide="book-open"
                                        class="h-3.5 w-3.5"
                                        aria-hidden="true"
                                    ></i>
                                </span>

                                <p class="mt-3 text-[10px] font-extrabold">
                                    Panduan Resmi
                                </p>

                                <p class="mt-1 text-[8px] leading-[1.45] text-muted-foreground">
                                    Informasi layanan lebih mudah dipahami.
                                </p>

                            </div>


                            {{-- Card 3 --}}
                            <div class="min-h-[108px] rounded-[15px] bg-[#fff8ed] p-3.5">

                                <span class="flex h-8 w-8 items-center justify-center rounded-[10px] bg-ocean/10 text-ocean">
                                    <i
                                        data-lucide="message-square"
                                        class="h-3.5 w-3.5"
                                        aria-hidden="true"
                                    ></i>
                                </span>

                                <p class="mt-3 text-[10px] font-extrabold">
                                    Bantuan Informasi
                                </p>

                                <p class="mt-1 text-[8px] leading-[1.45] text-muted-foreground">
                                    Asisten menjawab dari dokumen yang tersedia.
                                </p>

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =================================================
                    FUNGSI PORTAL
                ================================================== --}}
                <section
                    id="portal-pendampingan"
                    class="rounded-[24px] border border-[#eee7d7] bg-white p-6 shadow-sm sm:p-7"
                >

                    <div class="grid gap-6 lg:grid-cols-[.78fr_1.22fr] lg:items-center">

                        {{-- Text --}}
                        <div>

                            <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-teal">
                                PORTAL PENDAMPINGAN PESERTA
                            </span>

                            <h2 class="mt-2.5 text-[23px] font-extrabold leading-tight tracking-tight">
                                Apa fungsi portal ini?
                            </h2>

                            <p class="mt-2.5 text-[10px] leading-[1.7] text-muted-foreground">
                                Portal Pendampingan adalah ruang kerja pribadi Anda di dalam
                                Si-Molek. Di sini, Anda dipandu untuk memahami tahapan dan
                                mempersiapkan kebutuhan layanan sebelum menuju kanal
                                pengajuan resmi DKP.
                            </p>

                        </div>


                        {{-- 3 Steps --}}
                        <div class="grid gap-2.5 sm:grid-cols-3">

                            @foreach ([
                                ['01', 'Pilih layanan', 'Tentukan Magang/PKL atau WOPPS.'],
                                ['02', 'Siapkan kebutuhan', 'Pelajari checklist dan dokumen awal.'],
                                ['03', 'Pantau tahapan', 'Lihat posisi proses layanan Anda.'],
                            ] as [$number, $title, $description])

                                <div class="rounded-[15px] border border-[#eee7d7] bg-[#fffaf0] p-3">

                                    <span class="text-[9px] font-extrabold text-ocean">
                                        {{ $number }}
                                    </span>

                                    <h3 class="mt-4 text-[10px] font-extrabold leading-tight">
                                        {{ $title }}
                                    </h3>

                                    <p class="mt-1 text-[8px] leading-[1.45] text-muted-foreground">
                                        {{ $description }}
                                    </p>

                                </div>

                            @endforeach

                        </div>

                    </div>

                </section>


                {{-- =================================================
                    CARA PENGGUNAAN
                ================================================== --}}
                <section
                    id="cara-penggunaan"
                    class="rounded-[24px] bg-navy p-6 text-white shadow-sm sm:p-7"
                >

                    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

                        <div class="max-w-lg">

                            <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-teal-200">
                                CARA PENGGUNAAN
                            </span>

                            <h2 class="mt-2.5 text-[23px] font-extrabold leading-tight tracking-tight">
                                Empat langkah untuk memulai
                            </h2>

                            <p class="mt-2.5 text-[10px] leading-[1.7] text-blue-100">
                                Setelah memahami alurnya, lanjutkan ke bagian dashboard
                                di bawah untuk memulai persiapan.
                            </p>

                        </div>

                        <a
                            href="#ringkasan"
                            class="inline-flex w-fit items-center gap-2 rounded-full bg-white px-4 py-2 text-[10px] font-bold text-navy transition hover:bg-blue-50"
                        >
                            Mulai sekarang

                            <i
                                data-lucide="arrow-down"
                                class="h-3.5 w-3.5"
                                aria-hidden="true"
                            ></i>
                        </a>

                    </div>


                    <ol class="mt-6 grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4">

                        @foreach ([
                            ['Buat persiapan', 'Pilih jenis layanan yang dibutuhkan.'],
                            ['Baca checklist', 'Pahami data dan dokumen yang perlu disiapkan.'],
                            ['Gunakan panduan', 'Buka infografis atau Asisten jika membutuhkan informasi.'],
                            ['Lanjutkan proses', 'Ikuti tahapan sampai kanal pengajuan resmi tersedia.'],
                        ] as $index => [$title, $description])

                            <li class="rounded-[15px] border border-white/10 bg-white/[0.07] p-3">

                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-teal text-[9px] font-extrabold">
                                    {{ $index + 1 }}
                                </span>

                                <h3 class="mt-3 text-[10px] font-bold">
                                    {{ $title }}
                                </h3>

                                <p class="mt-1 text-[8px] leading-[1.45] text-blue-100">
                                    {{ $description }}
                                </p>

                            </li>

                        @endforeach

                    </ol>

                </section>


                {{-- =================================================
                    BELUM MEMILIKI APPLICATION
                ================================================== --}}
                @if (! $application)

                    <section
                        id="ringkasan"
                        class="overflow-hidden rounded-[24px] bg-gradient-to-br from-navy via-[#123d72] to-ocean p-6 text-white shadow-lg sm:p-8"
                    >

                        <div class="max-w-2xl">

                            <p class="text-[9px] font-bold uppercase tracking-[0.22em] text-teal-200">
                                PORTAL PESERTA SI-MOLEK
                            </p>

                            <h1 class="mt-2.5 text-[27px] font-extrabold tracking-tight">
                                Halo, {{ $participant->name }} 👋
                            </h1>

                            <p class="mt-2.5 max-w-xl text-[11px] leading-[1.7] text-blue-100">
                                Mulailah dengan memilih layanan yang ingin Anda persiapkan.
                                Si-Molek membantu menata kebutuhan sebelum Anda mengirim
                                pengajuan melalui Google Form resmi DKP.
                            </p>

                        </div>

                    </section>


                    {{-- Pilih layanan --}}
                    <section
                        id="persiapan"
                        class="rounded-[24px] border border-[#eee7d7] bg-white p-6 shadow-sm sm:p-7"
                    >

                        <div class="max-w-2xl">

                            <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-teal">
                                LANGKAH PERTAMA
                            </p>

                            <h2 class="mt-2 text-[21px] font-extrabold tracking-tight text-navy">
                                Pilih layanan yang akan dipersiapkan
                            </h2>

                            <p class="mt-2 text-[10px] leading-[1.7] text-muted-foreground">
                                Pilihan ini menentukan checklist awal Anda.
                                Ini belum merupakan pengajuan resmi ke Dinas.
                            </p>

                        </div>


                        <form
                            method="POST"
                            action="{{ route('peserta.application.store') }}"
                            class="mt-5"
                        >

                            @csrf

                            <fieldset>

                                <legend class="sr-only">
                                    Jenis layanan
                                </legend>

                                <div class="grid gap-3 md:grid-cols-2">

                                    @foreach ($serviceOptions as $value => $option)

                                        <label
                                            class="group cursor-pointer rounded-[18px] border border-border bg-[#fffaf0] p-4 transition hover:border-ocean/40 hover:bg-white has-[:checked]:border-ocean has-[:checked]:bg-ocean/[0.04]"
                                        >

                                            <input
                                                type="radio"
                                                name="service_type"
                                                value="{{ $value }}"
                                                class="peer sr-only"
                                                @checked(old('service_type') === $value)
                                            >

                                            <span class="flex items-start justify-between gap-4">

                                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean/10 text-ocean group-has-[:checked]:bg-ocean group-has-[:checked]:text-white">

                                                    <i
                                                        data-lucide="{{ $value === 'wopps' ? 'microscope' : 'graduation-cap' }}"
                                                        class="h-4 w-4"
                                                        aria-hidden="true"
                                                    ></i>

                                                </span>

                                                <span class="flex h-5 w-5 items-center justify-center rounded-full border-2 border-muted-foreground/30 peer-checked:border-ocean peer-checked:bg-ocean">

                                                    <i
                                                        data-lucide="check"
                                                        class="hidden h-3 w-3 text-white peer-checked:block"
                                                        aria-hidden="true"
                                                    ></i>

                                                </span>

                                            </span>

                                            <span class="mt-4 block text-[12px] font-extrabold text-navy">
                                                {{ $option['label'] }}
                                            </span>

                                            <span class="mt-1 block text-[10px] leading-[1.5] text-muted-foreground">
                                                {{ $option['description'] }}
                                            </span>

                                        </label>

                                    @endforeach

                                </div>

                            </fieldset>


                            @error('service_type')

                                <p class="mt-3 text-sm font-medium text-destructive">
                                    {{ $message }}
                                </p>

                            @enderror


                            <button
                                type="submit"
                                class="mt-5 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-ocean to-teal px-5 py-2.5 text-[10px] font-bold text-white shadow-md shadow-ocean/20 transition hover:brightness-110"
                            >
                                Buat Persiapan Pengajuan

                                <i
                                    data-lucide="arrow-right"
                                    class="h-3.5 w-3.5"
                                    aria-hidden="true"
                                ></i>

                            </button>

                        </form>

                    </section>


                @else

                    {{-- =================================================
                        APPLICATION SUDAH ADA
                    ================================================== --}}
                    <section
                        id="ringkasan"
                        class="overflow-hidden rounded-[24px] bg-gradient-to-br from-navy via-[#123d72] to-ocean p-6 text-white shadow-lg sm:p-8"
                    >

                        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">

                            <div class="max-w-2xl">

                                <p class="text-[9px] font-bold uppercase tracking-[0.22em] text-teal-200">
                                    {{ $application->serviceLabel() }}
                                </p>

                                <h1 class="mt-2.5 text-[27px] font-extrabold tracking-tight">
                                    Halo, {{ $participant->name }} 👋
                                </h1>

                                <p class="mt-2.5 text-[11px] leading-[1.7] text-blue-100">
                                    Anda sedang berada pada tahap persiapan pengajuan.
                                    Lengkapi kebutuhan terlebih dahulu sebelum menuju
                                    Google Form resmi DKP.
                                </p>

                            </div>


                            <div class="rounded-[18px] border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">

                                <p class="text-[8px] font-bold uppercase tracking-wider text-blue-200">
                                    Status saat ini
                                </p>

                                <p class="mt-1 text-[13px] font-extrabold">
                                    Persiapan Pengajuan
                                </p>

                            </div>

                        </div>


                        {{-- Progress --}}
                        <div class="mt-6">

                            <div class="flex items-center justify-between text-[9px] font-bold text-blue-100">
                                <span>
                                    Tahap 1 dari {{ count($steps) }}
                                </span>

                                <span>
                                    Persiapan
                                </span>
                            </div>

                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-white/15">

                                <div class="h-full w-1/5 rounded-full bg-teal"></div>

                            </div>

                        </div>

                    </section>


                    {{-- =================================================
                        WORKFLOW MAGANG
                    ================================================== --}}
                    @if ($application->service_type === \App\Models\ParticipantApplication::SERVICE_MAGANG_PKL)

                        @include('components.peserta.internship-workflow', [
                            'application' => $application,
                            'locations' => $internshipLocations,
                            'guestbookUrl' => $internshipGuestbookUrl,
                        ])

                    @else

                        {{-- =================================================
                            QUICK MENU
                        ================================================== --}}
                        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">

                            {{-- Checklist --}}
                            <a
                                href="#persiapan"
                                class="group rounded-[20px] border border-[#eee7d7] bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                            >

                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean/10 text-ocean">

                                    <i
                                        data-lucide="file-check-2"
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    ></i>

                                </span>

                                <span class="mt-4 block text-[11px] font-extrabold text-navy">
                                    Checklist Dokumen
                                </span>

                                <span class="mt-1 block text-[9px] leading-[1.5] text-muted-foreground">
                                    Pahami kebutuhan sebelum mengajukan.
                                </span>

                            </a>


                            {{-- AI --}}
                            <div class="rounded-[20px] border border-[#eee7d7] bg-white p-4 opacity-70 shadow-sm">

                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal/10 text-teal">

                                    <i
                                        data-lucide="scan-search"
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    ></i>

                                </span>

                                <span class="mt-4 block text-[11px] font-extrabold text-navy">
                                    AI Document Checker
                                </span>

                                <span class="mt-1 block text-[9px] leading-[1.5] text-muted-foreground">
                                    Segera hadir pada tahap berikutnya.
                                </span>

                            </div>


                            {{-- Google Form --}}
                            <div class="rounded-[20px] border border-[#eee7d7] bg-white p-4 opacity-70 shadow-sm">

                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean/10 text-ocean">

                                    <i
                                        data-lucide="external-link"
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    ></i>

                                </span>

                                <span class="mt-4 block text-[11px] font-extrabold text-navy">
                                    Google Form Resmi
                                </span>

                                <span class="mt-1 block text-[9px] leading-[1.5] text-muted-foreground">
                                    Terbuka setelah pemeriksaan dokumen tersedia.
                                </span>

                            </div>


                            {{-- Chatbot --}}
                            <a
                                href="{{ route('chatbot') }}"
                                class="group rounded-[20px] border border-[#eee7d7] bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                            >

                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal/10 text-teal">

                                    <i
                                        data-lucide="message-circle"
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    ></i>

                                </span>

                                <span class="mt-4 block text-[11px] font-extrabold text-navy">
                                    Tanya Asisten
                                </span>

                                <span class="mt-1 block text-[9px] leading-[1.5] text-muted-foreground">
                                    Cari informasi dari dokumen resmi DKP.
                                </span>

                            </a>

                        </section>


                        {{-- =================================================
                            CHECKLIST
                        ================================================== --}}
                        <section
                            id="persiapan"
                            class="grid gap-4 xl:grid-cols-[1.35fr_0.65fr]"
                        >

                            <article class="rounded-[24px] border border-[#eee7d7] bg-white p-6 shadow-sm sm:p-7">

                                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">

                                    <div>

                                        <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-teal">
                                            PERSIAPAN PENGAJUAN
                                        </p>

                                        <h2 class="mt-2 text-[21px] font-extrabold tracking-tight">
                                            Checklist dokumen awal
                                        </h2>

                                        <p class="mt-1.5 text-[10px] leading-[1.6] text-muted-foreground">
                                            Checklist ini membantu Anda menyiapkan dokumen.
                                            Status belum menjadi verifikasi resmi Dinas.
                                        </p>

                                    </div>

                                    <span class="inline-flex w-fit rounded-full bg-amber-50 px-2.5 py-1 text-[8px] font-bold text-amber-700">
                                        Belum ditinjau
                                    </span>

                                </div>


                                <div class="mt-5 divide-y divide-border rounded-[15px] border border-border">

                                    @foreach ($application->preparationChecklist() as $item)

                                        <div class="flex gap-3 px-4 py-3.5">

                                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground">

                                                <i
                                                    data-lucide="circle"
                                                    class="h-3.5 w-3.5"
                                                    aria-hidden="true"
                                                ></i>

                                            </span>

                                            <span>

                                                <span class="block text-[10px] font-bold text-navy">
                                                    {{ $item['label'] }}
                                                </span>

                                                <span class="mt-0.5 block text-[9px] leading-[1.5] text-muted-foreground">
                                                    {{ $item['description'] }}
                                                </span>

                                            </span>

                                        </div>

                                    @endforeach

                                </div>


                                <p class="mt-4 text-[8px] leading-[1.5] text-muted-foreground">
                                    Fitur unggah dan pemeriksaan dokumen akan ditambahkan
                                    setelah fondasi persiapan ini selesai.
                                </p>

                            </article>


                            {{-- Bantuan --}}
                            <aside class="rounded-[24px] border border-[#eee7d7] bg-[#fffaf0] p-6">

                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-ocean shadow-sm">

                                    <i
                                        data-lucide="circle-help"
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    ></i>

                                </span>

                                <h2 class="mt-4 text-[17px] font-extrabold">
                                    Butuh bantuan?
                                </h2>

                                <p class="mt-1.5 text-[10px] leading-[1.6] text-muted-foreground">
                                    Gunakan Asisten Si-Molek untuk menanyakan persyaratan,
                                    alur, atau kontak layanan.
                                </p>

                                <a
                                    href="{{ route('chatbot') }}"
                                    class="mt-4 inline-flex items-center gap-2 text-[10px] font-bold text-ocean transition hover:text-navy"
                                >
                                    Buka Asisten Si-Molek

                                    <i
                                        data-lucide="arrow-up-right"
                                        class="h-3.5 w-3.5"
                                        aria-hidden="true"
                                    ></i>

                                </a>

                            </aside>

                        </section>


                        {{-- =================================================
                            PROGRESS
                        ================================================== --}}
                        <section
                            id="progress"
                            class="rounded-[24px] border border-[#eee7d7] bg-white p-6 shadow-sm sm:p-7"
                        >

                            <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-teal">
                                MONITORING PENDAMPING
                            </p>

                            <h2 class="mt-2 text-[21px] font-extrabold tracking-tight">
                                Progress pengajuan
                            </h2>

                            <p class="mt-1.5 text-[10px] leading-[1.6] text-muted-foreground">
                                Status Dinas akan terlihat di sini ketika fitur monitoring
                                administrasi telah diaktifkan oleh pengelola.
                            </p>


                            <ol class="mt-5 grid gap-3 md:grid-cols-5">

                                @foreach ($steps as $index => $step)

                                    <li
                                        class="relative rounded-[15px] border p-3
                                        {{ $index === 0
                                            ? 'border-ocean bg-ocean/[0.04]'
                                            : 'border-border bg-[#fffaf0]' }}"
                                    >

                                        <span
                                            class="flex h-6 w-6 items-center justify-center rounded-full text-[9px] font-extrabold
                                            {{ $index === 0
                                                ? 'bg-ocean text-white'
                                                : 'bg-muted text-muted-foreground' }}"
                                        >
                                            {{ $index + 1 }}
                                        </span>

                                        <span class="mt-2.5 block text-[10px] font-extrabold text-navy">
                                            {{ $step['label'] }}
                                        </span>

                                        <span class="mt-1 block text-[8px] leading-[1.5] text-muted-foreground">
                                            {{ $step['description'] }}
                                        </span>

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