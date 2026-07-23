@php
$menu = [
    ['icon' => 'bar-chart-2', 'label' => 'Dashboard', 'route' => 'admin.dashboard'],
    ['icon' => 'database', 'label' => 'Knowledge Base', 'route' => 'admin.knowledge-base'],
    ['icon' => 'message-square', 'label' => 'Conversation Logs', 'route' => 'admin.conversation-logs'],
    ['icon' => 'inbox', 'label' => 'Pertanyaan Tidak Terjawab', 'route' => 'admin.unanswered-questions'],
    ['icon' => 'trending-up', 'label' => 'Analytics', 'route' => 'admin.analytics'],
    ['icon' => 'users', 'label' => 'Manajemen Admin', 'route' => 'admin.manajemen-admin'],
    ['icon' => 'activity', 'label' => 'Activity Log', 'route' => 'admin.activity-log'],
];
@endphp
<div data-admin-sidebar class="flex w-60 flex-shrink-0 flex-col overflow-hidden bg-navy transition-all duration-300">
<div class="flex items-center gap-2.5 border-b border-white/10 p-4">
<div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10">
<i data-lucide="fish" class="h-5 w-5 text-cyan-300" aria-hidden="true"></i>
</div>
<div>
<div class="text-sm font-bold leading-tight text-white">DKP Assistant</div>
<div class="text-[10px] leading-tight text-blue-300">Jawa Timur</div>
</div>
</div>
<nav class="flex-1 space-y-0.5 overflow-y-auto p-3">
@foreach ($menu as $item)
@php $active = $item['route'] && request()->routeIs($item['route']); @endphp
<a href="{{ $item['route'] ? route($item['route']) : '#' }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs font-medium transition-all {{ $active ? 'bg-ocean/50 text-white' : 'text-blue-300 hover:bg-white/8 hover:text-white' }}">
<i data-lucide="{{ $item['icon'] }}" class="h-[15px] w-[15px]" aria-hidden="true"></i>
<span class="truncate">{{ $item['label'] }}</span>
</a>
@endforeach
</nav>
<div class="border-t border-white/10 p-3">
<form method="POST" action="{{ route('admin.logout') }}">
@csrf
<button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs text-blue-300 transition-all hover:bg-white/8 hover:text-red-300">
<i data-lucide="log-out" class="h-[15px] w-[15px]" aria-hidden="true"></i>
Logout
</button>
</form>
</div>
</div>