@props(['title' => 'Dashboard'])

@php
$initials = collect(explode(' ', auth()->user()->name))
    ->map(fn ($w) => mb_substr($w, 0, 1))
    ->take(2)
    ->implode('');
@endphp

<div class="flex items-center gap-3 border-b border-border bg-white px-5 py-3">
    <button type="button" data-sidebar-toggle class="rounded-xl p-2 hover:bg-accent">
        <i data-lucide="menu" class="h-4 w-4" aria-hidden="true"></i>
    </button>

    <h1 class="text-sm font-semibold text-navy">{{ $title }}</h1>

    <div class="ml-auto flex items-center gap-2">
        <div class="relative hidden w-48 sm:block">
            <i data-lucide="search" class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" aria-hidden="true"></i>
            <input
                type="text"
                placeholder="Cari..."
                class="w-full rounded-xl border border-border bg-input-background py-2 pl-8 pr-3 text-xs focus:outline-none"
            >
        </div>

        <a href="{{ route('admin.unanswered-questions') }}" class="relative rounded-xl p-2 hover:bg-accent" aria-label="Notifikasi pertanyaan">
            <i data-lucide="bell" class="h-4 w-4" aria-hidden="true"></i>
            @if (($notifications['total'] ?? 0) > 0)
                <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white">{{ min($notifications['total'], 99) }}</span>
            @endif
        </a>

        <div class="flex items-center gap-2 rounded-xl border border-border bg-input-background px-2.5 py-1.5">
            <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-ocean text-[10px] font-bold text-white">
                {{ strtoupper($initials) }}
            </div>
            <span class="hidden text-xs font-medium text-navy sm:block">{{ auth()->user()->name }}</span>
        </div>
    </div>
</div>
