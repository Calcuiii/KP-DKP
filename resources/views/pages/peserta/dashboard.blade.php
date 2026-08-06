@extends('layouts.app')

@section('title', 'Portal Peserta | Si-Molek')

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

    <main class="min-h-screen bg-background font-sans text-navy">
        <header class="border-b border-border bg-white/95 px-5 py-4 backdrop-blur sm:px-8 lg:px-10">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-ocean to-teal text-white shadow-lg shadow-ocean/20">
                        <i data-lucide="compass" class="h-5 w-5" aria-hidden="true"></i>
                    </span>
                    <span>
                        <span class="block text-base font-extrabold tracking-tight">Si-Molek</span>
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

        <div class="mx-auto flex max-w-7xl gap-8 px-5 py-7 sm:px-8 lg:px-10">
            <aside class="hidden w-56 shrink-0 lg:block">
                <nav class="sticky top-7 rounded-3xl border border-border bg-white p-3 shadow-sm">
                    <a href="#ringkasan" class="flex items-center gap-3 rounded-2xl bg-ocean px-4 py-3 text-sm font-bold text-white">
                        <i data-lucide="layout-dashboard" class="h-4 w-4" aria-hidden="true"></i> Dashboard
                    </a>
                    <a href="#persiapan" class="mt-1 flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-muted-foreground transition hover:bg-light hover:text-navy">
                        <i data-lucide="file-check-2" class="h-4 w-4" aria-hidden="true"></i> Persiapan Dokumen
                    </a>
                    <a href="#progress" class="mt-1 flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-muted-foreground transition hover:bg-light hover:text-navy">
                        <i data-lucide="route" class="h-4 w-4" aria-hidden="true"></i> Status Pengajuan
                    </a>
                    <a href="{{ route('chatbot') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-muted-foreground transition hover:bg-light hover:text-navy">
                        <i data-lucide="message-circle" class="h-4 w-4" aria-hidden="true"></i> Tanya Asisten
                    </a>
                    <div class="mx-3 my-3 border-t border-border"></div>
                    <a href="{{ route('landing') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-muted-foreground transition hover:bg-light hover:text-navy">
                        <i data-lucide="house" class="h-4 w-4" aria-hidden="true"></i> Beranda Si-Molek
                    </a>
                </nav>
            </aside>

            <div class="min-w-0 flex-1 space-y-6">
                @if (session('status'))
                    <div class="flex items-start gap-3 rounded-2xl border border-teal/25 bg-teal/10 px-5 py-4 text-sm font-medium text-teal">
                        <i data-lucide="circle-check" class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if (! $application)
                    <section id="ringkasan" class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy via-[#123d72] to-ocean p-7 text-white shadow-xl shadow-navy/10 sm:p-10">
                        <div class="max-w-2xl">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-teal-200">Portal Peserta Si-Molek</p>
                            <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Halo, {{ $participant->name }} 👋</h1>
                            <p class="mt-3 max-w-xl text-sm leading-relaxed text-blue-100 sm:text-base">Mulailah dengan memilih layanan yang ingin Anda persiapkan. Si-Molek membantu menata kebutuhan sebelum Anda mengirim pengajuan melalui Google Form resmi DKP.</p>
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
                            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Gunakan Asisten Si-Molek untuk menanyakan persyaratan, alur, atau kontak layanan.</p>
                            <a href="{{ route('chatbot') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-ocean transition hover:text-navy">Buka Asisten Si-Molek <i data-lucide="arrow-up-right" class="h-4 w-4" aria-hidden="true"></i></a>
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
