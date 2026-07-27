@extends('layouts.app')

@section('title', 'Pusat Informasi Visual Magang dan PKL | DKP Assistant')

@section('meta_description', 'Jelajahi infografis dan surat edaran resmi mengenai layanan Magang dan PKL DKP Jawa Timur.')

@section('hide_dev_nav')
@endsection

@section('content')
    @php
        $journeyStyles = [
            ['border' => 'border-ocean/25', 'ring' => 'ring-ocean/15', 'text' => 'text-ocean', 'badge' => 'bg-ocean'],
            ['border' => 'border-teal/25', 'ring' => 'ring-teal/15', 'text' => 'text-teal', 'badge' => 'bg-teal'],
            ['border' => 'border-violet-500/25', 'ring' => 'ring-violet-500/15', 'text' => 'text-violet-600', 'badge' => 'bg-violet-500'],
            ['border' => 'border-amber-500/30', 'ring' => 'ring-amber-500/15', 'text' => 'text-amber-500', 'badge' => 'bg-amber-500'],
            ['border' => 'border-pink-500/25', 'ring' => 'ring-pink-500/15', 'text' => 'text-pink-500', 'badge' => 'bg-pink-500'],
            ['border' => 'border-sky-500/25', 'ring' => 'ring-sky-500/15', 'text' => 'text-sky-500', 'badge' => 'bg-sky-500'],
            ['border' => 'border-emerald-500/25', 'ring' => 'ring-emerald-500/15', 'text' => 'text-emerald-500', 'badge' => 'bg-emerald-500'],
            ['border' => 'border-purple-500/25', 'ring' => 'ring-purple-500/15', 'text' => 'text-purple-500', 'badge' => 'bg-purple-500'],
        ];
    @endphp

    <div class="min-h-screen bg-white font-sans">
        @include('components.landing.navbar')

        <main class="overflow-hidden">
            <section class="mx-auto max-w-7xl px-4 pb-12 pt-14 sm:px-6 sm:pt-20 lg:px-8">
                <div class="mx-auto max-w-4xl text-center">
                    <span class="inline-flex items-center gap-2 rounded-full bg-secondary px-4 py-2 text-xs font-bold uppercase tracking-[0.16em] text-ocean sm:text-sm">
                        <i data-lucide="info" class="h-4 w-4" aria-hidden="true"></i>
                        Pusat Informasi Visual
                    </span>

                    <h1 class="mt-6 text-4xl font-bold tracking-tight text-navy sm:text-5xl lg:text-6xl">
                        Jelajahi Informasi <span class="text-ocean">KP & Magang</span>
                    </h1>

                    <p class="mx-auto mt-5 max-w-3xl text-base leading-relaxed text-muted-foreground sm:text-lg">
                        Telusuri 7 seri infografis dan 1 surat edaran resmi untuk memahami informasi penting seputar Kerja Praktik dan Magang di Dinas Kelautan dan Perikanan Provinsi Jawa Timur.
                    </p>

                    <p class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-ocean">
                        <i data-lucide="info" class="h-4 w-4" aria-hidden="true"></i>
                        Pilih titik pada peta untuk melihat infografis
                    </p>
                </div>

                <div class="mt-10 grid gap-7 lg:grid-cols-[minmax(0,1fr)_25rem] lg:items-start">
                    <section
                        aria-label="Peta perjalanan infografis"
                        class="relative overflow-hidden rounded-[2rem] border border-ocean/15 bg-[linear-gradient(150deg,#edf6ff_0%,#f8fcff_55%,#eefbf7_100%)] px-5 py-10 shadow-sm sm:px-10 sm:py-14"
                    >
                        <div class="absolute inset-0 opacity-60" aria-hidden="true">
                            <div class="absolute left-[12%] top-[11%] h-10 w-10 rounded-full bg-ocean/5"></div>
                            <div class="absolute right-[9%] top-[29%] h-12 w-12 rounded-full bg-teal/5"></div>
                            <div class="absolute left-[8%] top-[53%] h-8 w-8 rounded-full bg-ocean/5"></div>
                            <div class="absolute right-[12%] top-[74%] h-11 w-11 rounded-full bg-teal/5"></div>
                        </div>

                        <svg
                            class="pointer-events-none absolute inset-x-0 top-20 hidden h-[calc(100%-10rem)] w-full lg:block"
                            viewBox="0 0 100 720"
                            preserveAspectRatio="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M50 12 C72 58 72 76 50 104 S28 150 50 192 S72 238 50 280 S28 326 50 368 S72 414 50 456 S28 502 50 544 S72 590 50 636 S28 676 50 710"
                                fill="none"
                                stroke="#8db8e7"
                                stroke-dasharray="3 3"
                                stroke-linecap="round"
                                stroke-width="1.2"
                            />
                        </svg>

                        <div class="relative mx-auto max-w-xl">
                            <div class="mx-auto flex h-28 w-28 flex-col items-center justify-center rounded-full bg-navy text-center text-white ring-8 ring-ocean/10 shadow-lg">
                                <span class="text-lg font-extrabold">MULAI</span>
                                <span class="mt-1 px-3 text-[10px] leading-tight text-white/75">Perjalanan informasi KP & Magang</span>
                            </div>

                            <div class="relative mt-8 space-y-6 lg:space-y-[-1rem]">
                                @foreach ($infographics as $index => $item)
                                    @php
                                        $style = $journeyStyles[$index];
                                        $isEven = $index % 2 === 0;
                                        $label = $item->type === 'infografis'
                                            ? sprintf('Seri %02d', $item->series_number)
                                            : 'Surat Resmi';
                                    @endphp

                                    <div class="flex {{ $isEven ? 'justify-end lg:pr-6' : 'justify-start lg:pl-6' }}">
                                        <button
                                            id="infographic-journey-{{ $item->id }}"
                                            type="button"
                                            data-infographic-lightbox-trigger
                                            data-infographic-index="{{ $index }}"
                                            data-image-src="{{ $item->image_url }}"
                                            data-image-alt="{{ $item->alt }}"
                                            data-image-caption="{{ $item->caption }}"
                                            data-image-width="{{ $item->image_width }}"
                                            data-image-height="{{ $item->image_height }}"
                                            class="group relative flex h-36 w-36 flex-col items-center justify-center rounded-full border-2 bg-white px-3 text-center shadow-sm ring-8 transition duration-200 hover:-translate-y-1 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-ocean focus:ring-offset-4 sm:h-40 sm:w-40 {{ $style['border'] }} {{ $style['ring'] }}"
                                            aria-label="Buka {{ $item->caption }} ukuran penuh"
                                        >
                                            <span class="text-3xl font-extrabold tracking-tight {{ $style['text'] }}">
                                                {{ str_pad((string) $item->position, 2, '0', STR_PAD_LEFT) }}
                                            </span>

                                            <span class="mt-1 max-w-[7.5rem] text-xs font-bold text-navy">
                                                {{ $label }}
                                            </span>

                                            <span class="mt-1 line-clamp-2 max-w-[7.5rem] text-[10px] leading-tight text-muted-foreground">
                                                {{ $item->alt }}
                                            </span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mx-auto mt-9 flex h-11 w-11 items-center justify-center rounded-full border-2 border-teal bg-white text-teal shadow-sm ring-8 ring-teal/10" aria-label="Selesai">
                                <i data-lucide="check-circle" class="h-6 w-6" aria-hidden="true"></i>
                            </div>
                        </div>
                    </section>

                    <aside class="space-y-5 lg:sticky lg:top-24">
                        <section class="rounded-3xl border border-border bg-white p-6 shadow-sm">
                            <h2 class="flex items-center gap-2 text-lg font-bold text-navy">
                                <i data-lucide="info" class="h-5 w-5 text-ocean" aria-hidden="true"></i>
                                Cara Menggunakan
                            </h2>

                            <ol class="mt-5 space-y-4 text-sm leading-relaxed text-muted-foreground">
                                <li class="flex gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ocean text-xs font-bold text-white">1</span>
                                    <span>Pilih titik pada peta perjalanan.</span>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ocean text-xs font-bold text-white">2</span>
                                    <span>Baca infografis atau surat resmi berukuran penuh.</span>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ocean text-xs font-bold text-white">3</span>
                                    <span>Gunakan tombol berikutnya untuk melanjutkan urutan informasi.</span>
                                </li>
                            </ol>
                        </section>

                        <section class="rounded-3xl border border-border bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-bold text-navy">Akses Cepat</h2>

                            <nav class="mt-4 space-y-1" aria-label="Akses cepat infografis">
                                @foreach ($infographics as $index => $item)
                                    @php
                                        $style = $journeyStyles[$index];
                                        $label = $item->type === 'infografis'
                                            ? sprintf('Seri %02d', $item->series_number)
                                            : 'Surat Resmi';
                                    @endphp

                                    <a
                                        href="#infographic-journey-{{ $item->id }}"
                                        class="flex items-center gap-3 rounded-xl px-2 py-2.5 text-left transition hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-ocean"
                                    >
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {{ $style['badge'] }}">
                                            {{ str_pad((string) $item->position, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-semibold text-navy">{{ $label }}</span>
                                            <span class="block truncate text-xs text-muted-foreground">{{ $item->alt }}</span>
                                        </span>
                                        <i data-lucide="arrow-right" class="h-4 w-4 shrink-0 text-muted-foreground" aria-hidden="true"></i>
                                    </a>
                                @endforeach
                            </nav>
                        </section>

                        <section class="rounded-3xl border border-teal/20 bg-teal/5 p-6 text-sm leading-relaxed text-muted-foreground">
                            <i data-lucide="shield" class="h-5 w-5 text-teal" aria-hidden="true"></i>
                            <p class="mt-3">
                                Seluruh informasi visual bersumber dari dokumen dan informasi resmi yang digunakan dalam knowledge base DKP Assistant.
                            </p>
                            <a href="{{ route('landing') }}#infografis" class="mt-4 inline-flex items-center gap-2 font-semibold text-ocean hover:underline">
                                Kembali ke beranda
                                <i data-lucide="arrow-right" class="h-4 w-4" aria-hidden="true"></i>
                            </a>
                        </section>
                    </aside>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
                <div class="rounded-[2rem] bg-[linear-gradient(105deg,#0c2340,#1a5fa8)] px-6 py-10 text-center shadow-lg sm:px-12 sm:py-12">
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">Masih Belum Menemukan Informasi yang Dicari?</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-white/70 sm:text-base">
                        Tanyakan langsung kepada DKP Assistant berdasarkan informasi resmi yang tersedia.
                    </p>
                    <a
                        href="{{ route('chatbot') }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl border-2 border-white/35 px-6 py-3 text-sm font-bold text-white transition hover:border-white hover:bg-white/10"
                    >
                        <i data-lucide="message-square" class="h-5 w-5" aria-hidden="true"></i>
                        Mulai Bertanya
                    </a>
                </div>
            </section>
        </main>

        @include('components.landing.footer')
        @include('components.infographics.lightbox')
    </div>
@endsection
