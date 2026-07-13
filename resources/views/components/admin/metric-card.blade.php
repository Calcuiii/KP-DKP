@props(['icon', 'label', 'value', 'sub' => null, 'color' => 'ocean'])

@php
$colorMap = [
    'ocean'  => ['text' => 'text-ocean', 'bg' => 'bg-ocean/10'],
    'teal'   => ['text' => 'text-teal', 'bg' => 'bg-teal/10'],
    'indigo' => ['text' => 'text-indigo-500', 'bg' => 'bg-indigo-50'],
    'amber'  => ['text' => 'text-amber-500', 'bg' => 'bg-amber-50'],
    'red'    => ['text' => 'text-red-500', 'bg' => 'bg-red-50'],
][$color] ?? ['text' => 'text-ocean', 'bg' => 'bg-ocean/10'];
@endphp

<div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-muted-foreground">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold {{ $colorMap['text'] }}">{{ $value }}</p>
            @if ($sub)
                <p class="mt-1 text-xs text-muted-foreground">{{ $sub }}</p>
            @endif
        </div>
        <div class="rounded-xl p-2.5 {{ $colorMap['bg'] }}">
            <i data-lucide="{{ $icon }}" class="h-5 w-5 {{ $colorMap['text'] }}" aria-hidden="true"></i>
        </div>
    </div>
</div>