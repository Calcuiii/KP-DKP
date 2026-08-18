@extends('layouts.app')
@section('title', 'Asisten SI-MELAYUR | Informasi Layanan')
 
@section(
    'meta_description',
    'Tanyakan informasi layanan Kerja Praktik, Magang, PKL, dan WOPPS melalui Asisten SI-MELAYUR berdasarkan dokumen resmi.'
)
@section('hide_dev_nav', true)

@section('content')
    <div
        data-chatbot-app
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
            class="fixed inset-0 z-30 hidden bg-navy/40 lg:hidden"
        ></div>

        <main class="flex min-w-0 flex-1 flex-col">
            @include('components.chatbot.header')

            @include('components.chatbot.empty-state')

            <section
                data-chat-messages
                class="chatbot-pattern-surface hidden min-h-0 flex-1 overflow-y-auto px-4 py-6 sm:px-6"
            >
                <div data-chat-message-list class="chatbot-pattern-content mx-auto w-full max-w-3xl space-y-5"></div>
            </section>

            @include('components.chatbot.chat-input')
        </main>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/chatbot.js')
@endpush
