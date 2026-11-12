@extends('layouts.app')

@section('title', 'Buku Tamu | SI-MELAYUR')
@section('meta_description', 'Verifikasi Buku Tamu sebelum menggunakan asisten informasi SI-MELAYUR.')
@section('hide_dev_nav', 'true')

@section('content')
    <div class="min-h-screen bg-slate-50 font-sans">
        @include('components.landing.navbar')
        <main class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-lg flex-col justify-center px-4 py-10 sm:px-6 sm:py-14">
            <a href="{{ route('chatbot.service') }}" class="mb-5 inline-flex w-fit items-center gap-2 rounded text-sm text-muted-foreground transition-colors hover:text-ocean focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ocean">
                <span aria-hidden="true">←</span> Pilih layanan lain
            </a>

            @include('components.guestbook.checkin-card')
        </main>
    </div>
@endsection
