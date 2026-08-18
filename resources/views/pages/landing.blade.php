@extends('layouts.app')

@section('title', 'SI-MELAYUR | Magang, Penelitian, dan Data Kelautan Jawa Timur')

@section(
    'meta_description',
    'SI-MELAYUR adalah Sistem Informasi Magang, Penelitian, dan Data Kelautan Jawa Timur milik Dinas Kelautan dan Perikanan Provinsi Jawa Timur.'
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
