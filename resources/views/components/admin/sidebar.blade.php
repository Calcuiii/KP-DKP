@php
    $user = auth()->user();
    $isSuperAdmin = $user?->isSuperAdmin();
    $initials = collect(explode(' ', $user?->name ?? 'Admin'))->filter()->map(fn ($word) => mb_substr($word, 0, 1))->take(2)->implode('');
    $groups = [
        ['label' => 'Portal Peserta', 'icon' => 'briefcase-business', 'active' => request()->routeIs('admin.pemeriksaan-dokumen*', 'admin.internship-locations*', 'admin.surat-balasan*', 'admin.wopps-follow-up*'), 'items' => array_values(array_filter([
            $isSuperAdmin ? ['icon' => 'file-check-2', 'label' => 'Pemeriksaan Dokumen', 'route' => 'admin.pemeriksaan-dokumen', 'active' => ['admin.pemeriksaan-dokumen', 'admin.pemeriksaan-dokumen.*']] : null,
            ['icon' => 'map-pinned', 'label' => 'Kuota Lokasi Magang', 'route' => 'admin.internship-locations', 'active' => ['admin.internship-locations', 'admin.internship-locations.*']],
            $isSuperAdmin ? ['icon' => 'file-output', 'label' => 'Surat Balasan', 'route' => 'admin.surat-balasan', 'active' => ['admin.surat-balasan', 'admin.surat-balasan.*']] : null,
            $isSuperAdmin ? ['icon' => 'phone-call', 'label' => 'Tindak Lanjut WOPPS', 'route' => 'admin.wopps-follow-up', 'active' => ['admin.wopps-follow-up', 'admin.wopps-follow-up.*']] : null,
        ]))],
        ['label' => 'Asisten SI-MELAYUR', 'icon' => 'messages-square', 'active' => request()->routeIs('admin.knowledge-base*', 'admin.infographics*', 'admin.unanswered-questions*', 'admin.conversation-logs*', 'admin.analytics*'), 'items' => array_values(array_filter([
            $isSuperAdmin ? ['icon' => 'database', 'label' => 'Knowledge Base', 'route' => 'admin.knowledge-base', 'active' => ['admin.knowledge-base', 'admin.knowledge-base.*']] : null,
            $isSuperAdmin ? ['icon' => 'images', 'label' => 'Infografis', 'route' => 'admin.infographics', 'active' => ['admin.infographics', 'admin.infographics.*']] : null,
            ['icon' => 'circle-help', 'label' => 'Pertanyaan Tidak Terjawab', 'route' => 'admin.unanswered-questions', 'active' => ['admin.unanswered-questions', 'admin.unanswered-questions.*']],
            ['icon' => 'message-square-text', 'label' => 'Riwayat Percakapan', 'route' => 'admin.conversation-logs', 'active' => ['admin.conversation-logs', 'admin.conversation-logs.*']],
            ['icon' => 'chart-no-axes-combined', 'label' => 'Analitik Chatbot', 'route' => 'admin.analytics', 'active' => ['admin.analytics', 'admin.analytics.*']],
        ]))],
    ];
@endphp

<aside id="admin-sidebar" data-admin-sidebar class="flex w-64 flex-shrink-0 flex-col overflow-hidden border-r border-slate-200 bg-white text-slate-600 transition-[width] duration-300">
    <div class="flex h-[65px] items-center gap-3 border-b border-slate-100 px-5">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-navy text-white shadow-sm"><i data-lucide="fish" class="h-5 w-5" aria-hidden="true"></i></span>
        <span class="min-w-0"><span class="block truncate text-[15px] font-extrabold tracking-tight text-navy">SI-MELAYUR</span><span class="block text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">Admin Portal</span></span>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 py-5" aria-label="Navigasi administrator">
        <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Menu</p>
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-[13px] font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-navy text-white shadow-sm' : 'hover:bg-slate-100 hover:text-navy' }}">
            <i data-lucide="layout-dashboard" class="h-[17px] w-[17px] shrink-0" aria-hidden="true"></i><span>Dashboard</span>
        </a>

        <div class="mt-1.5 space-y-1.5">
            @foreach ($groups as $group)
                <details class="group overflow-hidden rounded-xl {{ $group['active'] ? 'bg-[#f5f7fb]' : '' }}" {{ $group['active'] ? 'open' : '' }}>
                    <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-3 text-[13px] font-semibold transition hover:bg-slate-100 hover:text-navy [&::-webkit-details-marker]:hidden {{ $group['active'] ? 'bg-navy text-white hover:bg-navy hover:text-white' : '' }}">
                        <i data-lucide="{{ $group['icon'] }}" class="h-[17px] w-[17px] shrink-0" aria-hidden="true"></i><span class="min-w-0 flex-1">{{ $group['label'] }}</span><i data-lucide="chevron-down" class="h-3.5 w-3.5 shrink-0 transition-transform duration-200 group-open:rotate-180" aria-hidden="true"></i>
                    </summary>
                    <div class="relative ml-[19px] space-y-0.5 border-l border-slate-200 py-1.5 pl-3 pr-1">
                        @foreach ($group['items'] as $item)
                            @php $active = request()->routeIs(...$item['active']); @endphp
                            <a href="{{ route($item['route']) }}" class="flex items-center gap-2.5 rounded-lg px-2.5 py-2.5 text-xs font-medium leading-snug transition {{ $active ? 'bg-white font-bold text-navy shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:bg-white hover:text-navy' }}">
                                <i data-lucide="{{ $item['icon'] }}" class="h-3.5 w-3.5 shrink-0 {{ $active ? 'text-ocean' : 'text-slate-400' }}" aria-hidden="true"></i><span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </div>

        <div class="my-4 border-t border-slate-100"></div>
        <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Sistem</p>
        <div class="space-y-1">
            <a href="{{ route('admin.activity-log') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-[13px] font-semibold transition {{ request()->routeIs('admin.activity-log*') ? 'bg-navy text-white shadow-sm' : 'hover:bg-slate-100 hover:text-navy' }}"><i data-lucide="history" class="h-[17px] w-[17px] shrink-0" aria-hidden="true"></i><span>Log Aktivitas</span></a>
            @if ($isSuperAdmin)
                <a href="{{ route('admin.manajemen-admin') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-[13px] font-semibold transition {{ request()->routeIs('admin.manajemen-admin*') ? 'bg-navy text-white shadow-sm' : 'hover:bg-slate-100 hover:text-navy' }}"><i data-lucide="users-round" class="h-[17px] w-[17px] shrink-0" aria-hidden="true"></i><span>Manajemen Admin</span></a>
            @endif
        </div>
    </nav>

    <div class="p-4 pt-2">
        <div class="rounded-2xl bg-slate-100 p-3">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-ocean text-[10px] font-extrabold text-white">{{ strtoupper($initials) }}</span>
                <span class="min-w-0 flex-1"><span class="block truncate text-xs font-bold text-navy">{{ $user?->name ?? 'Administrator' }}</span><span class="block truncate text-[9px] text-slate-400">{{ $isSuperAdmin ? 'Super Administrator' : 'Administrator' }}</span></span>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="mt-3 border-t border-slate-200 pt-2">@csrf
                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-[10px] font-semibold text-slate-500 transition hover:bg-white hover:text-red-600"><i data-lucide="log-out" class="h-3.5 w-3.5" aria-hidden="true"></i><span>Keluar</span></button>
            </form>
        </div>
    </div>
</aside>