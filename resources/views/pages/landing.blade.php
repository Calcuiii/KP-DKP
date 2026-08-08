@extends('layouts.app')

@section('title', 'Si-Molek | Layanan KP, Magang, PKL, dan WOPPS')

@section(
    'meta_description',
    'Si-Molek adalah Sistem Informasi Manajemen Otomatisasi Layanan Kerja Praktik, Magang, PKL, dan WOPPS Dinas Kelautan dan Perikanan Provinsi Jawa Timur.'
)

@section('content')
    <div class="min-h-screen bg-white font-sans">

        @include('components.landing.navbar')

        <main>
            @include('components.landing.hero')

            @include('components.landing.categories')

            @include('components.landing.infographics-preview', [
                'items' => $infographics,
            ])

            @include('components.landing.benefits')

            @include('components.landing.faq')

            @include('components.landing.cta')
        </main>

        @include('components.landing.footer')

    </div>
@endsection
