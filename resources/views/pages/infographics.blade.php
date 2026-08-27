@extends('layouts.app')

@section('title', 'Pusat Informasi Visual Layanan DKP | SI-MELAYUR')

@section('meta_description', 'Jelajahi infografis layanan Magang, KP, PKL, WOPPS, dan surat edaran resmi DKP Jawa Timur.')

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

        $activeService = request()->query('layanan') === 'wopps' ? 'wopps' : 'magang';
        $isWopps = $activeService === 'wopps';
        $visibleInfographics = $infographics
            ->filter(fn ($item) => $isWopps
                ? $item->type === \App\Models\Infographic::TYPE_WOPPS
                : $item->type !== \App\Models\Infographic::TYPE_WOPPS)
            ->values();
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
                        Jelajahi Informasi <span class="text-ocean">Layanan DKP</span>
                    </h1>

                    <p class="mx-auto mt-5 max-w-3xl text-base leading-relaxed text-muted-foreground sm:text-lg">
                        Pilih jenis layanan agar infografis Magang, KP, dan PKL terpisah dari infografis khusus WOPPS.
                    </p>

                    <nav class="mx-auto mt-7 inline-flex gap-1 rounded-xl border border-ocean/15 bg-secondary/70 p-1 shadow-sm" aria-label="Pilih kategori infografis">
                        <a
                            href="{{ route('infographics', ['layanan' => 'magang']) }}"
                            class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-bold transition {{ ! $isWopps ? 'bg-ocean text-white shadow-sm' : 'text-ocean hover:bg-white' }}"
                            @if (! $isWopps) aria-current="page" @endif
                        >
                            Magang / KP / PKL
                        </a>
                        <a
                            href="{{ route('infographics', ['layanan' => 'wopps']) }}"
                            class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-bold transition {{ $isWopps ? 'bg-teal text-white shadow-sm' : 'text-teal hover:bg-white' }}"
                            @if ($isWopps) aria-current="page" @endif
                        >
                            WOPPS
                        </a>
                    </nav>

                </div>

                @if ($isWopps)
                    @php
                        $woppsInfographic = $visibleInfographics->first();
                    @endphp

                    <section class="mx-auto mt-10 max-w-5xl rounded-[2rem] border border-border bg-[linear-gradient(145deg,#f7fbfd,#eef9f6)] p-4 shadow-sm sm:p-6 lg:p-8" aria-labelledby="wopps-showcase-title">
                        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)] lg:items-center">
                            <div class="rounded-[1.5rem] border border-teal/15 bg-white p-3 shadow-sm sm:p-4">
                                @if ($woppsInfographic)
                                    <img src="{{ $woppsInfographic->image_url }}" alt="{{ $woppsInfographic->alt }}" width="{{ $woppsInfographic->image_width }}" height="{{ $woppsInfographic->image_height }}" class="mx-auto aspect-[3/4] w-full max-w-md rounded-xl object-cover">
                                @endif
                            </div>

                            <div class="px-1 py-2 sm:px-3">
                                <span class="inline-flex w-fit items-center rounded-full bg-teal/10 px-2.5 py-1.5 text-[11px] font-extrabold uppercase tracking-[0.14em] text-teal">Infografis WOPPS</span>
                                <h2 id="wopps-showcase-title" class="mt-4 text-3xl font-extrabold leading-tight text-navy sm:text-4xl">Persyaratan Dokumen Pengajuan</h2>
                                <p class="mt-3 text-sm font-semibold leading-relaxed text-teal sm:text-base">Wawancara, Observasi, Penelitian, Permintaan Data, dan Sampling</p>
                                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Periksa panduan dokumen dan tautan formulir resmi sebelum mengajukan layanan WOPPS.</p>

                                <div class="mt-5 divide-y divide-border rounded-lg border border-border bg-white px-2.5 text-sm text-navy">
                                    <p class="py-2.5">Periksa seluruh persyaratan dokumen.</p>
                                    <p class="py-2.5">Akses tautan formulir resmi pada infografis.</p>
                                </div>

                                @if ($woppsInfographic)
                                    <button type="button" data-infographic-lightbox-trigger data-lightbox-minimal="true" data-infographic-index="0" data-image-src="{{ $woppsInfographic->image_url }}" data-image-alt="{{ $woppsInfographic->alt }}" data-image-caption="{{ $woppsInfographic->caption }}" data-image-service="{{ $woppsInfographic->display_label }}" data-image-width="{{ $woppsInfographic->image_width }}" data-image-height="{{ $woppsInfographic->image_height }}" class="mt-5 inline-flex items-center rounded-lg bg-teal px-3 py-2 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-teal/20">
                                        Buka Infografis
                                    </button>
                                @endif
                            </div>
                        </div>
                    </section>
                @else
                <div class="mt-10 grid gap-7 lg:grid-cols-[minmax(0,1fr)_25rem] lg:items-start">
                    <section aria-label="Timeline infografis Magang" class="relative overflow-hidden rounded-[2rem] border border-ocean/15 bg-[#fbfcf7] px-5 py-10 shadow-sm sm:px-8 sm:py-12">
                        <div class="relative mx-auto max-w-2xl">
                            <div class="mx-auto max-w-sm rounded-2xl bg-ocean px-6 py-5 text-center text-white shadow-md">
                                <span class="text-xs font-extrabold uppercase tracking-[0.16em] text-blue-100">Panduan Visual</span>
                                <h2 class="mt-1 text-lg font-extrabold">Infografis Magang / KP / PKL</h2>
                            </div>

                            <div class="relative mt-7 space-y-5 before:absolute before:bottom-0 before:left-5 before:top-0 before:w-1 before:rounded-full before:bg-ocean/35 sm:before:left-1/2 sm:before:-translate-x-1/2">
                                @foreach ($visibleInfographics as $index => $item)
                                    @php
                                        $style = $journeyStyles[$index];
                                        $isEven = $index % 2 === 0;
                                        $label = $item->display_label;
                                    @endphp

                                    <div class="relative grid items-center gap-4 pl-12 sm:grid-cols-[1fr_2.5rem_1fr] sm:gap-3 sm:pl-0">
                                        <span class="absolute left-5 top-1/2 z-10 h-4 w-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-4 border-white {{ $style['badge'] }} shadow sm:static sm:col-start-2 sm:row-start-1 sm:mx-auto sm:translate-x-0 sm:translate-y-0" aria-hidden="true"></span>
                                        <button
                                            id="infographic-journey-{{ $item->id }}"
                                            type="button"
                                            data-infographic-lightbox-trigger
                                            data-infographic-index="{{ $index }}"
                                            data-image-src="{{ $item->image_url }}"
                                            data-image-alt="{{ $item->alt }}"
                                            data-image-caption="{{ $item->caption }}"
                                            data-image-service="{{ $item->display_label }}"
                                            data-image-width="{{ $item->image_width }}"
                                            data-image-height="{{ $item->image_height }}"
                                            class="group w-full rounded-2xl border bg-white px-5 py-4 text-left shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-ocean focus:ring-offset-2 {{ $style['border'] }} {{ $isEven ? 'sm:col-start-1 sm:row-start-1 sm:text-right' : 'sm:col-start-3 sm:row-start-1' }}"
                                            aria-label="Buka {{ $item->caption }} ukuran penuh"
                                        >
                                            <span class="text-xs font-extrabold uppercase tracking-[0.12em] {{ $style['text'] }}">{{ $label }}</span>
                                            <span class="mt-2 block text-sm font-bold leading-snug text-navy">{{ $item->alt }}</span>
                                            <span class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-muted-foreground">Lihat infografis <i data-lucide="arrow-up-right" class="h-3 w-3" aria-hidden="true"></i></span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mx-auto mt-7 max-w-xs rounded-2xl bg-teal px-6 py-4 text-center text-white shadow-md" aria-label="Selesai">
                                <span class="text-sm font-extrabold">Informasi Siap Dipelajari</span>
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
                                    <span>Pilih kartu pada timeline infografis.</span>
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
                                @foreach ($visibleInfographics as $index => $item)
                                    @php
                                        $style = $journeyStyles[$index];
                                        $label = $item->display_label;
                                    @endphp

                                    <a
                                        href="#infographic-journey-{{ $item->id }}"
                                        class="flex items-center gap-3 rounded-xl px-2 py-2.5 text-left transition hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-ocean"
                                    >
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {{ $style['badge'] }}">
                                            {{ $item->series_number ? str_pad((string) $item->series_number, 2, '0', STR_PAD_LEFT) : 'SE' }}
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
                                Seluruh informasi visual bersumber dari dokumen dan informasi resmi yang digunakan dalam knowledge base SI-MELAYUR.
                            </p>
                            <a href="{{ route('landing') }}#infografis" class="mt-4 inline-flex items-center gap-2 font-semibold text-ocean hover:underline">
                                Kembali ke beranda
                                <i data-lucide="arrow-right" class="h-4 w-4" aria-hidden="true"></i>
                            </a>
                        </section>
                    </aside>
                </div>
                @endif
            </section>

            <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
                <div class="rounded-[2rem] bg-[linear-gradient(105deg,#0c2340,#1a5fa8)] px-6 py-10 text-center shadow-lg sm:px-12 sm:py-12">
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">Masih Belum Menemukan Informasi yang Dicari?</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-white/70 sm:text-base">
                        Tanyakan langsung kepada SI-MELAYUR berdasarkan informasi resmi yang tersedia.
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
