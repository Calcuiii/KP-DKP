@props(['color' => 'blue'])

@php
$colors = [
    'blue' => 'bg-blue-100 text-blue-700',
    'green' => 'bg-emerald-100 text-emerald-700',
];
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium {{ $colors[$color] ?? $colors['blue'] }}">
    {{ $slot }}
</span>