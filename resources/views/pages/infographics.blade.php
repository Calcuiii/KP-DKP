@extends('layouts.app')

@section('title', 'Infografis Magang dan PKL | DKP Assistant')

@section('meta_description', 'Kumpulan infografis dan surat edaran resmi mengenai layanan Magang dan PKL DKP Jawa Timur.')

@section('content')
    <div class="min-h-screen bg-background font-sans">
        @include('components.landing.navbar')

        <main class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <a
                    href="{{ route('landing') }}#infografis"
                    class="text-sm font-semibold text-ocean hover:underline"
                >
                    ← Kembali ke Beranda
                </a>

                <span class="mt-8 block text-sm font-semibold text-teal">Panduan visual</span>

                <h1 class="mt-2 text-4xl font-bold text-navy sm:text-5xl">
                    Infografis Magang dan PKL
                </h1>

                <p class="mt-4 leading-relaxed text-muted-foreground">
                    Telusuri seluruh seri infografis dan surat edaran resmi dalam urutan yang telah ditetapkan.
                </p>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($infographics as $index => $item)
                    <x-infographics.card :item="$item" :index="$index" loading="lazy" />
                @endforeach
            </div>
        </main>

        @include('components.infographics.lightbox')
    </div>
@endsection
