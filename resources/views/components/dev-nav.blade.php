<div class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 flex items-center gap-1 rounded-full border border-border bg-white p-1.5 shadow-lg">
    <a href="{{ route('landing') }}"
       class="rounded-full px-4 py-2 text-sm font-semibold transition-colors {{ request()->routeIs('landing') ? 'bg-navy text-white' : 'text-muted-foreground hover:text-navy' }}">
        Landing
    </a>

    <a href="{{ route('landing') }}#chatbot"
       class="rounded-full px-4 py-2 text-sm font-semibold transition-colors text-muted-foreground hover:text-navy">
        Chat
    </a>

    <a href="{{ route('admin.login') }}"
       class="rounded-full px-4 py-2 text-sm font-semibold transition-colors {{ request()->routeIs('admin.login') ? 'bg-navy text-white' : 'text-muted-foreground hover:text-navy' }}">
        Admin Login
    </a>

    <a href="{{ route('admin.dashboard') }}"
        class="rounded-full px-4 py-2 text-sm font-semibold transition-colors {{ request()->routeIs('admin.*') && !request()->routeIs('admin.login') ? 'bg-navy text-white' : 'text-muted-foreground hover:text-navy' }}">
        Dashboard
    </a>
</div>