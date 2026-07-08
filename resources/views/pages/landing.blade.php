@extends('layouts.app')

@section('title', 'DKP Assistant | Informasi KP dan Magang')

@section(
    'meta_description',
    'Temukan informasi Kerja Praktik dan Magang melalui DKP Assistant berbasis informasi resmi Dinas Kelautan dan Perikanan Provinsi Jawa Timur.'
)

@section('content')
    <div class="min-h-screen bg-white font-sans">

        @include('components.landing.navbar')

        <main>
            @include('components.landing.hero')

            @include('components.landing.categories')

            @include('components.landing.popular-questions')

            @include('components.landing.how-it-works')

            @include('components.landing.benefits')

            @include('components.landing.faq')

            @include('components.landing.cta')
        </main>

        @include('components.landing.footer')

    </div>
@endsection