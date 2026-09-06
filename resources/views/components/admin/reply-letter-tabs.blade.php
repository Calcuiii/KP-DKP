@php
    $isWopps = request()->routeIs('admin.wopps-follow-up*');
@endphp

<nav class="grid w-full max-w-[24rem] grid-cols-2 rounded-2xl border border-slate-200 bg-slate-100/80 p-1 shadow-inner" aria-label="Jenis layanan surat balasan">
    <a
        href="{{ route('admin.surat-balasan') }}"
        class="flex min-h-9 items-center justify-center gap-1.5 rounded-xl px-2.5 py-2 text-center text-xs font-bold transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ocean/40 focus-visible:ring-offset-2 sm:px-4 {{ !$isWopps ? 'bg-ocean text-white shadow-[0_4px_12px_rgba(30,100,180,0.18)]' : 'text-slate-500 hover:bg-white/70 hover:text-navy' }}"
        @if(!$isWopps) aria-current="page" @endif
    >
        <i data-lucide="briefcase-business" class="h-3.5 w-3.5 shrink-0" aria-hidden="true"></i>
        Magang / KP / PKL
    </a>
    <a
        href="{{ route('admin.wopps-follow-up') }}"
        class="flex min-h-9 items-center justify-center gap-1.5 rounded-xl px-2.5 py-2 text-center text-xs font-bold transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ocean/40 focus-visible:ring-offset-2 sm:px-4 {{ $isWopps ? 'bg-ocean text-white shadow-[0_4px_12px_rgba(30,100,180,0.18)]' : 'text-slate-500 hover:bg-white/70 hover:text-navy' }}"
        @if($isWopps) aria-current="page" @endif
    >
        <i data-lucide="clipboard-list" class="h-3.5 w-3.5 shrink-0" aria-hidden="true"></i>
        WOPPS
    </a>
</nav>
