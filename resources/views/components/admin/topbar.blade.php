@props(['title' => 'Dashboard'])

@php
$initials = collect(explode(' ', auth()->user()->name))
    ->map(fn ($w) => mb_substr($w, 0, 1))
    ->take(2)
    ->implode('');
@endphp

<div class="flex items-center gap-3 border-b border-border bg-white px-5 py-3">
    <button type="button" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="true" aria-label="Sembunyikan menu admin" class="rounded-xl p-2 hover:bg-accent">
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

        <div class="relative" data-admin-notification-center>
            <button
                type="button"
                data-admin-notification-toggle
                aria-expanded="false"
                aria-haspopup="true"
                class="relative rounded-xl p-2 hover:bg-accent"
            >
                <i data-lucide="bell" class="h-4 w-4" aria-hidden="true"></i>
                @php $unreadAdminCount = auth()->user()->unreadNotifications->count(); @endphp
                @if ($unreadAdminCount > 0)
                    <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white">{{ min($unreadAdminCount, 99) }}</span>
                @endif
            </button>

            <div
                data-admin-notification-panel
                class="absolute right-0 top-12 z-50 hidden w-80 rounded-2xl border border-border bg-white shadow-xl sm:w-96"
            >
                <div class="flex items-center justify-between border-b border-border px-5 py-4">
                    <div>
                        <p class="text-sm font-bold text-navy">Notifikasi</p>
                        <p class="text-xs text-muted-foreground">Pembaruan portal peserta &amp; chatbot</p>
                    </div>
                    @if ($unreadAdminCount > 0)
                        <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-ocean">Tandai semua dibaca</button>
                        </form>
                    @endif
                </div>

                <div class="max-h-96 overflow-y-auto">
                    @forelse (auth()->user()->notifications()->latest()->take(10)->get() as $notif)
                        <form method="POST" action="{{ route('admin.notifications.read', $notif->id) }}" class="border-b border-border last:border-b-0">
                            @csrf
                            <button type="submit" class="flex w-full items-start gap-3 px-5 py-4 text-left transition hover:bg-light/70 {{ $notif->read_at ? 'bg-white' : 'bg-ocean/[0.035]' }}">
                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl {{ $notif->data['type'] === 'document_automated_check_passed' ? 'bg-emerald-50 text-emerald-600' : 'bg-ocean/10 text-ocean' }}">
                                    <i data-lucide="{{ match($notif->data['type'] ?? null) {
                                        'internship_form_submitted' => 'clipboard-check',
                                        'document_automated_check_passed' => 'file-check',
                                        default => 'bell',
                                    } }}" class="h-4 w-4" aria-hidden="true"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-xs font-bold text-navy">{{ $notif->data['title'] ?? 'Notifikasi' }}</span>
                                    <span class="mt-1 block text-[11px] leading-relaxed text-muted-foreground">{{ $notif->data['message'] ?? '' }}</span>
                                    <span class="mt-2 block text-[10px] font-semibold text-ocean">{{ $notif->created_at->diffForHumans() }}</span>
                                </span>
                            </button>
                        </form>
                    @empty
                        <p class="px-5 py-10 text-center text-xs text-muted-foreground">Belum ada notifikasi.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 rounded-xl border border-border bg-input-background px-2.5 py-1.5">
            <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-ocean text-[10px] font-bold text-white">
                {{ strtoupper($initials) }}
            </div>
            <span class="hidden text-xs font-medium text-navy sm:block">{{ auth()->user()->name }}</span>
        </div>
    </div>
</div>