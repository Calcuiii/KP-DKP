@extends('layouts.app')
@section('title', 'Asisten SI-MELAYUR | Informasi Layanan')
 
@section(
    'meta_description',
    'Tanyakan informasi layanan Kerja Praktik, Magang, PKL, dan WOPPS melalui Asisten SI-MELAYUR berdasarkan dokumen resmi.'
)
@section('hide_dev_nav', true)

@section('content')
    @php
        $chatGuest = ! auth('peserta')->check() && ! auth('web')->check();
        $guestService = $chatGuest ? session('chatbot_guest_service') : null;
        $chatAllowed = ! $chatGuest || $guestService === 'wopps' || ($guestService === 'magang_pkl' && (int) session('guestbook_verified_until', 0) > now()->timestamp);
    @endphp
    <div
        data-chatbot-app
        data-chat-session-scope="{{ auth('peserta')->check() ? hash_hmac('sha256', 'peserta:'.auth('peserta')->id(), config('app.key')) : (auth('web')->check() ? hash_hmac('sha256', 'admin:'.auth('web')->id(), config('app.key')) : 'guest') }}"
        data-guest-service="{{ $guestService }}"
        data-chat-guest="{{ $chatGuest ? 'true' : 'false' }}"
        data-chat-allowed="{{ $chatAllowed ? 'true' : 'false' }}"
        data-service-choice-url="{{ route('chatbot.service.store') }}"
        data-guestbook-url="{{ route('guestbook.checkin') }}"
        data-history-url="{{ route('chatbot.api.history') }}"
        data-send-url="{{ route('chatbot.api.messages.send') }}"
        data-escalate-url-template="{{ route('chatbot.api.messages.escalate', ['message' => '__MESSAGE__']) }}"
        data-conversation-url-template="{{ route('chatbot.api.conversation', ['conversation' => '__CONVERSATION__']) }}"
        data-infographics-url="{{ route('infographics') }}"
        data-contact-url="{{ config('services.dkp.contact_whatsapp_url') }}"
        class="flex h-screen overflow-hidden bg-light font-sans text-navy"
    >
        @include('components.chatbot.sidebar')

        <div
            data-chat-sidebar-overlay
            class="chatbot-sidebar-overlay fixed inset-0 z-30 bg-navy/40 lg:hidden"
        ></div>

        <main class="chatbot-ocean relative isolate flex min-w-0 flex-1 flex-col bg-[#edf8fa]">
            <img src="{{ asset('images/si-melayur-chatbot-ocean.png') }}" alt="" aria-hidden="true" width="1536" height="1024" fetchpriority="high" class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover object-bottom">
            @include('components.chatbot.header')

            @include('components.chatbot.empty-state')

            <section
                data-chat-messages
                class="hidden min-h-0 flex-1 overflow-y-auto px-4 py-6 sm:px-6"
            >
                <div data-chat-message-list class="chatbot-pattern-content mx-auto w-full max-w-3xl space-y-5"></div>
            </section>

            @include('components.chatbot.chat-input')
        </main>
        @if ($chatGuest)
            @include('components.guestbook.modal')
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/chatbot.js')
@endpush
