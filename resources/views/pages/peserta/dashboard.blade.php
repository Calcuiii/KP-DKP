@extends('layouts.app')

@section('title', request()->routeIs('peserta.activities') ? 'Kegiatan Magang | SI-MELAYUR' : 'Portal Peserta | SI-MELAYUR')

@section('hide_dev_nav', true)

@section('content')
    @php
        $participant = auth('peserta')->user();
        $isActivitiesPage = request()->routeIs('peserta.activities');
        $steps = [
            ['label' => 'Persiapan', 'description' => 'Pilih layanan dan pahami kebutuhan dokumen.'],
            ['label' => 'Pemeriksaan dokumen', 'description' => 'Unggah dan periksa dokumen sebelum pengajuan.'],
            ['label' => 'Pengajuan resmi', 'description' => 'Isi Google Form resmi DKP.'],
            ['label' => 'Menunggu surat balasan', 'description' => 'Status resmi diperbarui oleh Dinas.'],
            ['label' => 'Pelaksanaan & penyelesaian', 'description' => 'Pantau informasi akhir kegiatan.'],
        ];
        $sidebarProgress = [
            ['label' => 'Pilih layanan', 'state' => $application ? 'done' : 'current'],
        ];

        if ($application) {
            if ($application->service_type === \App\Models\ParticipantApplication::SERVICE_MAGANG_PKL) {
                $letterApproved = $application->requestLetterApproved();
                $internshipFormCompleted = $application->google_form_confirmed_at !== null
                    && $application->latestDocument(\App\Models\ParticipantApplicationDocument::TYPE_INTERNSHIP_FORM_PROOF);
                $hasResponse = filled($application->response_letter_path) || filled($application->decision);
                $sidebarProgress = [
                    ['label' => 'Buku Tamu', 'state' => $application->guestbook_confirmed_at ? 'done' : 'current'],
                    ['label' => 'Upload Surat', 'state' => $application->letter_submitted_at ? 'done' : ($application->guestbook_confirmed_at ? 'current' : 'upcoming')],
                    ['label' => 'Pemeriksaan', 'state' => $letterApproved ? 'done' : ($application->letter_submitted_at ? 'current' : 'upcoming')],
                    ['label' => 'Form Resmi', 'state' => $internshipFormCompleted ? 'done' : ($letterApproved ? 'current' : 'upcoming')],
                    ['label' => 'Surat Balasan', 'state' => $hasResponse ? 'done' : ($internshipFormCompleted ? 'current' : 'upcoming')],
                    ['label' => 'Pelaksanaan, laporan & presentasi', 'state' => $application->presentation_submitted_at ? 'done' : (($hasResponse || $application->official_started_at) ? 'current' : 'upcoming'), 'target' => '#penyelesaian'],
                    ['label' => 'Form Selesai Magang', 'state' => ! $application->presentation_submitted_at ? 'upcoming' : ($application->completion_form_submitted_at ? 'done' : 'current'), 'target' => '#form-selesai'],
                    ['label' => 'Penerimaan Sertifikat', 'state' => ! ($application->presentation_submitted_at && $application->completion_form_submitted_at) ? 'upcoming' : ($application->certificate_published_at ? 'done' : 'current'), 'target' => '#penerimaan-sertifikat'],
                ];
            } else {
                $letterApproved = $application->requestLetterApproved();
                $ethicsApproved = $application->ethicsApprovalApproved();
                $woppsFormCompleted = $application->google_form_confirmed_at !== null;
                $woppsDecisionMade = filled($application->decision);
                $woppsFinished = $application->completed_at !== null || $application->decision === 'rejected';
                $sidebarProgress = [
                    ['label' => 'Pemeriksaan Surat', 'state' => $letterApproved ? 'done' : 'current'],
                    ['label' => 'Ethics Approval', 'state' => $ethicsApproved ? 'done' : ($letterApproved ? 'current' : 'upcoming')],
                    ['label' => 'Form WOPPS', 'state' => $woppsFormCompleted ? 'done' : ($ethicsApproved ? 'current' : 'upcoming')],
                    ['label' => 'Surat Balasan', 'state' => $woppsDecisionMade ? 'done' : ($woppsFormCompleted ? 'current' : 'upcoming')],
                    ['label' => 'Tindak Lanjut', 'state' => $woppsFinished ? 'done' : ($woppsDecisionMade ? 'current' : 'upcoming')],
                ];
            }
        }

        $activeProgressIndex = collect($sidebarProgress)->search(fn ($item) => $item['state'] === 'current');
        $activeProgressIndex = $activeProgressIndex === false ? max(0, count($sidebarProgress) - 1) : $activeProgressIndex;
        $activeProgressLabel = $sidebarProgress[$activeProgressIndex]['label'];
        $activeStatusLabel = match ($application?->status) {
            'letter_revision_required' => 'Revisi Surat Diperlukan',
            'letter_resubmission_required' => 'Menunggu Upload Ulang Surat',
            'letter_approved_no_certificate' => 'Disetujui tanpa Sertifikat',
            'ethics_revision_required' => 'Revisi Ethics Approval Diperlukan',
            'ethics_under_review' => 'Ethics Approval Sedang Diperiksa',
            'wopps_waiting_contact' => 'Menunggu Dihubungi Dinas',
            'wopps_contacted' => 'Sudah Dihubungi Dinas',
            'completed' => 'Layanan Selesai',
            'rejected' => 'Pengajuan Ditolak',
            default => $activeProgressLabel,
        };
        $isInternshipExecution = $application
            && $application->service_type === \App\Models\ParticipantApplication::SERVICE_MAGANG_PKL
            && ($application->official_started_at !== null || in_array(mb_strtolower((string) $application->decision), ['accepted', 'approved', 'diterima'], true));
        $portalNavigation = $isInternshipExecution
            ? [
                ['href' => '#ringkasan', 'icon' => 'home', 'label' => 'Dashboard Pelaksanaan', 'enabled' => true],
                ['href' => route('peserta.activities'), 'icon' => 'calendar-range', 'label' => 'Kalender & Tata Tertib', 'enabled' => true],
                ['href' => '#kenali-si-molek', 'icon' => 'info', 'label' => 'Informasi Portal', 'enabled' => true],
            ]
            : [
                ['href' => '#kenali-si-molek', 'icon' => 'info', 'label' => 'Informasi Portal', 'enabled' => true],
                ['href' => '#ringkasan', 'icon' => 'home', 'label' => 'Dashboard Saya', 'enabled' => true],
                ['href' => '#persiapan', 'icon' => 'file-check', 'label' => 'Persiapan Dokumen', 'enabled' => true],
                ['href' => '#progress', 'icon' => 'trending-up', 'label' => 'Status Pengajuan', 'enabled' => (bool) $application],
            ];
        if ($isActivitiesPage) {
            // Keep profile navigation available across both portal pages.
            foreach ($portalNavigation as &$item) {
                $item['href'] = str_starts_with($item['href'], '#') ? route('peserta.dashboard').$item['href'] : $item['href'];
            }
            unset($item);
        }
    @endphp

    <main class="participant-workspace min-h-screen bg-background font-sans text-navy">
        <header class="sticky top-0 z-40 border-b border-border bg-white/90 px-5 py-4 backdrop-blur-xl sm:px-8 lg:px-10">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <x-brand />
                </a>

                <div class="flex items-center gap-3">
                    <details class="relative" data-participant-account>
                        <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl p-2 hover:bg-light focus-visible:outline-2 focus-visible:outline-ocean" aria-label="Menu akun {{ $participant->name }}">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal/10 text-sm font-bold text-teal" aria-hidden="true">{{ mb_strtoupper(mb_substr($participant->name, 0, 1)) }}</span>
                            <span class="hidden max-w-48 text-left sm:block"><span class="block truncate text-sm font-bold">{{ $participant->name }}</span><span class="block text-xs text-muted-foreground">Peserta terverifikasi</span></span>
                            <i data-lucide="chevron-down" class="h-4 w-4" aria-hidden="true"></i>
                        </summary>
                        <div class="absolute right-0 top-full z-50 mt-2 w-60 rounded-2xl border border-border bg-white p-2 shadow-xl">
                            <p class="truncate border-b border-border px-3 py-3 text-xs text-muted-foreground">{{ $participant->email }}</p>
                            <form method="POST" action="{{ route('peserta.logout') }}">@csrf<button type="submit" class="w-full rounded-xl px-3 py-3 text-left text-sm font-semibold text-red-700 hover:bg-red-50">Keluar</button></form>
                        </div>
                    </details>
                    <div class="relative" data-notification-center>
                        <button type="button" data-notification-toggle aria-expanded="false" aria-controls="participant-notification-panel" aria-label="Buka notifikasi" class="relative flex h-10 w-10 items-center justify-center rounded-full border border-border bg-white text-muted-foreground shadow-sm transition hover:border-ocean/30 hover:text-ocean">
                            <i data-lucide="bell" class="h-4.5 w-4.5" aria-hidden="true"></i>
                            @if ($unreadNotificationCount > 0)
                                <span class="absolute -right-1 -top-1 flex min-h-5 min-w-5 items-center justify-center rounded-full bg-destructive px-1 text-[9px] font-bold text-white ring-2 ring-white">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>
                            @endif
                        </button>

                        <div id="participant-notification-panel" data-notification-panel class="absolute right-0 top-12 z-50 hidden w-[min(24rem,calc(100vw-2.5rem))] overflow-hidden rounded-3xl border border-border bg-white shadow-2xl shadow-navy/15">
                            <div class="flex items-center justify-between gap-4 border-b border-border px-5 py-4">
                                <div>
                                    <h2 class="text-sm font-bold text-navy">Notifikasi</h2>
                                    <p class="mt-0.5 text-[11px] text-muted-foreground">Pembaruan pemeriksaan dokumen Anda</p>
                                </div>
                                @if ($unreadNotificationCount > 0)
                                    <span class="rounded-full bg-ocean/10 px-2.5 py-1 text-[10px] font-bold text-ocean">{{ $unreadNotificationCount }} baru</span>
                                @endif
                            </div>

                            <div class="max-h-[25rem] overflow-y-auto">
                                @forelse ($participantNotifications as $notification)
                                    @php
                                    $notificationData = $notification->data;
                                    $notificationType = $notificationData['type'] ?? 'document_review';

                                        $isApprovedNotification =
                                            ($notificationData['status'] ?? '') === 'approved';

                                        $isReplyLetterNotification =
                                            $notificationType === 'reply_letter';
                                    @endphp
                                    <form method="POST" action="{{ route('peserta.notifications.read', $notification->id) }}" class="border-b border-border last:border-b-0">
                                        @csrf
                                        <button type="submit" class="group flex w-full items-start gap-3 px-5 py-4 text-left transition hover:bg-light/70 {{ $notification->read_at ? 'bg-white' : 'bg-ocean/[0.035]' }}">
                                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl {{ $isReplyLetterNotification ? 'bg-ocean/10 text-ocean' : ($isApprovedNotification ? 'bg-teal/10 text-teal' : 'bg-amber-100 text-amber-600') }}">
                                                <i data-lucide="{{ $isReplyLetterNotification ? 'mail' : ($isApprovedNotification ? 'circle-check' : 'alert-circle') }}" class="h-4 w-4" aria-hidden="true"></i>
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="mt-1 block text-[11px] leading-relaxed text-muted-foreground">{{ $notificationData['message'] ?? '' }}</span>
                                                @if (filled($notificationData['review_notes'] ?? null))
                                                    <span class="mt-2 block rounded-xl bg-light px-3 py-2 text-[11px] font-medium leading-relaxed text-navy"><strong>Catatan admin:</strong> {{ $notificationData['review_notes'] }}</span>
                                                @endif
                                                <span class="mt-2 block text-[10px] font-semibold text-ocean">
    {{ $notification->created_at->diffForHumans() }}
    ·
    {{ $notificationType === 'certificate' ? 'Unduh sertifikat' : ($isReplyLetterNotification ? 'Lihat surat balasan' : 'Lihat dokumen') }}
</span>
                                        </button>
                                    </form>
                                @empty
                                    <div class="px-6 py-10 text-center">
                                        <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-light text-muted-foreground"><i data-lucide="bell" class="h-5 w-5"></i></span>
                                        <p class="mt-3 text-xs font-bold text-navy">Belum ada notifikasi</p>
                                        <p class="mt-1 text-[11px] text-muted-foreground">Pembaruan dari admin akan muncul di sini.</p>
                                    </div>
                                @endforelse
                            </div>

                            @if ($unreadNotificationCount > 0)
                                <form method="POST" action="{{ route('peserta.notifications.read-all') }}" class="border-t border-border p-3">
                                    @csrf
                                    <button type="submit" class="w-full rounded-xl px-3 py-2 text-xs font-bold text-ocean transition hover:bg-ocean/5">Tandai semua sudah dibaca</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <nav class="border-b border-border bg-white px-5 py-3 lg:hidden" aria-label="Navigasi portal peserta">
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach ($portalNavigation as $navItem)
                    @if ($navItem['enabled'])
                        <a href="{{ $navItem['href'] }}" data-participant-nav class="participant-mobile-nav inline-flex shrink-0 items-center gap-2 rounded-full border border-border bg-white px-4 py-2 text-xs font-bold text-muted-foreground">
                            <i data-lucide="{{ $navItem['icon'] }}" class="h-3.5 w-3.5"></i>{{ $navItem['label'] }}
                        </a>
                    @else
                        <span aria-disabled="true" class="inline-flex shrink-0 cursor-not-allowed items-center gap-2 rounded-full border border-border/70 bg-light/70 px-4 py-2 text-xs font-bold text-muted-foreground/55">
                            <i data-lucide="lock" class="h-3.5 w-3.5"></i>Status belum tersedia
                        </span>
                    @endif
                @endforeach
            </div>
        </nav>

        <div class="mx-auto flex max-w-[90rem] gap-6 px-5 py-7 sm:px-8 lg:px-10">
            <aside class="hidden w-64 shrink-0 lg:block">
                <nav class="participant-sidebar sticky top-24 overflow-hidden rounded-[2rem] bg-gradient-to-b from-[#173f78] to-navy p-3 text-white shadow-xl shadow-navy/15">
                    <div class="px-3 pb-4 pt-3">
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-200">Menu Portal</span>
                        <p class="mt-1 text-sm font-bold">Informasi dan ruang kerja</p>
                    </div>
                    @foreach ($portalNavigation as $navItem)
                        @if ($navItem['enabled'])
                            <a href="{{ $navItem['href'] }}" data-participant-nav class="participant-sidebar-link flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold text-blue-100 transition">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10"><i data-lucide="{{ $navItem['icon'] }}" class="h-4 w-4"></i></span>
                                <span class="min-w-0 flex-1">{{ $navItem['label'] }}</span>
                            </a>
                        @else
                            <div aria-disabled="true" class="flex cursor-not-allowed items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold text-blue-300/55">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/5 bg-white/[0.04]"><i data-lucide="lock" class="h-4 w-4"></i></span>
                                <span class="min-w-0 flex-1">Status belum tersedia</span>
                            </div>
                        @endif
                    @endforeach

                    <div class="mx-3 my-4 border-t border-white/10"></div>
                    <div class="mx-2 rounded-2xl border border-white/10 bg-white/[0.07] p-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-blue-200">Progress Anda</span>
                            <span class="rounded-full bg-white/10 px-2 py-1 text-[9px] font-bold text-blue-100">{{ collect($sidebarProgress)->where('state', 'done')->count() }}/{{ count($sidebarProgress) }}</span>
                        </div>
                        <ol class="mt-4">
                            @foreach ($sidebarProgress as $index => $progressItem)
                                <li class="relative flex min-h-12 gap-3 pb-3 last:min-h-0 last:pb-0">
                                    @if (! $loop->last)
                                        <span class="absolute left-[0.69rem] top-6 h-[calc(100%-0.35rem)] w-px {{ $progressItem['state'] === 'done' ? 'bg-teal' : 'bg-white/15' }}"></span>
                                    @endif
                                    <span class="relative z-10 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-[10px] font-bold {{ $progressItem['state'] === 'done' ? 'border-teal bg-teal text-white' : ($progressItem['state'] === 'current' ? 'border-white bg-white text-ocean ring-4 ring-white/10' : 'border-white/20 bg-navy/40 text-blue-300') }}">
                                        @if ($progressItem['state'] === 'done')
                                            <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </span>
                                    @if ($isInternshipExecution && isset($progressItem['target']))
                                        <a href="{{ (($isActivitiesPage) ? route('peserta.dashboard') : '') . $progressItem['target'] }}" class="pt-0.5 text-[11px] font-bold leading-snug text-blue-100 underline-offset-4 hover:underline focus-visible:underline">{{ $progressItem['label'] }}</a>
                                    @else
                                        <span class="pt-0.5 text-[11px] font-bold leading-snug {{ $progressItem['state'] === 'upcoming' ? 'text-blue-300' : 'text-white' }}">{{ $progressItem['label'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                        @if (! $application)
                            <p class="mt-4 border-t border-white/10 pt-3 text-[10px] leading-relaxed text-blue-200">Pilih satu layanan untuk membuka alur dan status pengajuan Anda.</p>
                        @endif
                    </div>
                    <a href="{{ route('landing') }}" class="mx-2 mb-1 mt-2 flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-blue-200 transition hover:bg-white/10 hover:text-white"><i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Beranda SI-MELAYUR</a>
                </nav>
            </aside>

            <div class="participant-dashboard-content min-w-0 flex-1 space-y-6">
                @if (session('status'))
                    <div class="flex items-start gap-3 rounded-2xl border border-teal/25 bg-teal/10 px-5 py-4 text-sm font-medium text-teal">
                        <i data-lucide="circle-check" class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if (! $participant->institution || ! $participant->phone)
                    <form method="POST" action="{{ route('peserta.profile.details') }}" class="space-y-4 rounded-2xl border border-border bg-white p-6">
                        @csrf @method('PATCH')
                        <h2 class="text-lg font-bold">Lengkapi data akun Anda</h2>
                        @include('components.peserta.institution-fields')
                        <button class="rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white">Simpan data akun</button>
                    </form>
                @endif
                @unless ($isInternshipExecution)
                <section id="kenali-si-molek" class="relative isolate overflow-hidden rounded-[2rem] bg-[#dff3f7] px-4 py-8 text-navy shadow-sm sm:px-8 sm:py-10">
                    <img src="{{ asset('images/si-melayur-welcome.png') }}" alt="" aria-hidden="true" fetchpriority="high" width="1536" height="1024" class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover object-bottom">
                    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-white/80 via-white/20 to-transparent"></div>
                    <div class="relative mx-auto max-w-3xl text-center">
                        <span class="inline-flex rounded-full border border-teal/20 bg-white/80 px-4 py-2 text-[11px] font-bold text-teal">MULAI DARI SINI</span>
                        <h1 class="mt-5 text-3xl font-bold sm:text-4xl">Selamat datang di SI-MELAYUR, {{ $participant->name }}!</h1>
                        <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed sm:text-base">SI-MELAYUR adalah <strong>Sistem Informasi Magang, Penelitian, dan Data Kelautan Jawa Timur</strong> milik Dinas Kelautan dan Perikanan Provinsi Jawa Timur. Nama ini terinspirasi dari ikan layur sebagai identitas bahari DKP Jawa Timur.</p>
                    </div>

                    <div id="portal-pendampingan" class="relative mt-9 scroll-mt-28 overflow-hidden rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#edf8ff] via-white to-[#e7f8f5] px-5 py-7 text-navy shadow-2xl shadow-navy/15 sm:px-8 sm:py-9">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                            <div class="max-w-3xl">
                                <span class="inline-flex items-center gap-2 rounded-full bg-teal/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-teal">
                                    <i data-lucide="map" class="h-3.5 w-3.5"></i> Panduan Portal Peserta
                                </span>
                                <h2 class="mt-4 text-2xl font-bold sm:text-3xl">Empat langkah menggunakan portal</h2>
                                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-muted-foreground">Pilih layanan, siapkan kebutuhan, gunakan panduan saat diperlukan, lalu pantau proses sampai layanan Anda selesai.</p>
                            </div>
                            <a href="#ringkasan" class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full bg-navy px-5 py-3 text-xs font-bold text-white shadow-lg shadow-navy/15 transition hover:-translate-y-0.5 hover:bg-ocean">Mulai persiapan <i data-lucide="arrow-down" class="h-3.5 w-3.5"></i></a>
                        </div>

                        <ol id="cara-penggunaan" class="mt-7 grid scroll-mt-28 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ([
                                ['target', 'Pilih layanan', 'Tentukan layanan Magang/PKL atau WOPPS yang ingin Anda gunakan.'],
                                ['clipboard-check', 'Siapkan kebutuhan', 'Baca checklist dan lengkapi dokumen sesuai layanan pilihan.'],
                                ['book-open', 'Gunakan panduan', 'Cari informasi resmi melalui panduan atau Asisten SI-MELAYUR.'],
                                ['trending-up', 'Pantau proses', 'Ikuti status dan tindak lanjut yang tersedia hingga layanan selesai.'],
                            ] as $index => [$icon, $title, $description])
                                <li class="relative rounded-2xl border border-ocean/10 bg-white/90 p-4 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-ocean to-teal text-white"><i data-lucide="{{ $icon }}" class="h-4 w-4"></i></span>
                                        <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-ocean/60">Langkah {{ $index + 1 }}</span>
                                    </div>
                                    <h3 class="mt-4 text-sm font-bold">{{ $title }}</h3>
                                    <p class="mt-1 text-xs leading-relaxed text-muted-foreground">{{ $description }}</p>
                                    @if ($index < 3)
                                        <span class="absolute -right-2 top-1/2 z-10 hidden h-5 w-5 -translate-y-1/2 items-center justify-center rounded-full bg-white text-ocean shadow-sm xl:flex"><i data-lucide="chevron-right" class="h-3 w-3"></i></span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>
                @endunless

                @if ($isActivitiesPage)
                    @include('components.peserta.activity-page')
                @elseif ($isInternshipExecution)
                    @include('components.peserta.execution-dashboard', [
                        'application' => $application,
                        'participant' => $participant,
                        'sidebarProgress' => $sidebarProgress,
                    ])
                @elseif (! $application)
                    <section id="ringkasan" class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy via-[#123d72] to-ocean p-7 text-white shadow-xl shadow-navy/10 sm:p-10">
                        <div class="max-w-2xl">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-teal-200">Portal Peserta SI-MELAYUR</p>
                            <h2 class="mt-3 text-3xl font-bold sm:text-4xl">Mulai persiapan pengajuan Anda</h2>
                            <p class="mt-3 max-w-xl text-sm leading-relaxed text-blue-100 sm:text-base">Mulailah dengan memilih layanan yang ingin Anda persiapkan. SI-MELAYUR membantu menata kebutuhan sebelum Anda mengirim pengajuan melalui Google Form resmi DKP.</p>
                        </div>
                    </section>

                    <section id="persiapan" class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
                        <div class="max-w-2xl">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">Langkah pertama</p>
                            <h2 class="mt-2 text-2xl font-bold text-navy">Pilih layanan yang akan dipersiapkan</h2>
                            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Pilihan ini menentukan checklist awal Anda. Ini belum merupakan pengajuan resmi ke Dinas.</p>
                        </div>

                        <form method="POST" action="{{ route('peserta.application.store') }}" class="mt-6">
                            @csrf
                            <fieldset>
                                <legend class="sr-only">Jenis layanan</legend>
                                <div class="grid gap-4 md:grid-cols-2">
                                    @foreach ($serviceOptions as $value => $option)
                                        <label class="group cursor-pointer rounded-3xl border border-border bg-light/60 p-5 transition hover:border-ocean/40 hover:bg-white has-[:checked]:border-ocean has-[:checked]:bg-ocean/[0.04]">
                                            <input type="radio" name="service_type" value="{{ $value }}" class="peer sr-only" @checked(old('service_type') === $value)>
                                            <span class="flex items-start justify-between gap-4">
                                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-ocean/10 text-ocean group-has-[:checked]:bg-ocean group-has-[:checked]:text-white">
                                                    <i data-lucide="{{ $value === 'wopps' ? 'microscope' : 'graduation-cap' }}" class="h-5 w-5" aria-hidden="true"></i>
                                                </span>
                                                <span class="flex h-5 w-5 items-center justify-center rounded-full border-2 border-muted-foreground/30 peer-checked:border-ocean peer-checked:bg-ocean">
                                                    <i data-lucide="check" class="hidden h-3 w-3 text-white peer-checked:block" aria-hidden="true"></i>
                                                </span>
                                            </span>
                                            <span class="mt-5 block text-base font-bold text-navy">{{ $option['label'] }}</span>
                                            <span class="mt-1 block text-sm leading-relaxed text-muted-foreground">{{ $option['description'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            @error('service_type')
                                <p class="mt-3 text-sm font-medium text-destructive">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="mt-6 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-ocean to-teal px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-ocean/20 transition hover:brightness-110">
                                Buat Persiapan Pengajuan
                                <i data-lucide="arrow-right" class="h-4 w-4" aria-hidden="true"></i>
                            </button>
                        </form>
                    </section>
                @else
                    <section id="ringkasan" class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy via-[#123d72] to-ocean p-7 text-white shadow-xl shadow-navy/10 sm:p-10">
                        <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                            <div class="max-w-2xl">
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-teal-200">{{ $application->serviceLabel() }}</p>
                                <h2 class="mt-3 text-3xl font-bold sm:text-4xl">Pantau pengajuan Anda</h2>
                                <p class="mt-3 text-sm leading-relaxed text-blue-100 sm:text-base">Tahap aktif Anda adalah <strong class="text-white">{{ $activeStatusLabel }}</strong>. Ikuti langkah yang ditandai untuk melanjutkan proses layanan.</p>
                            </div>
                            <div class="rounded-3xl border border-white/15 bg-white/10 px-5 py-4 backdrop-blur-sm">
                                <p class="text-xs font-bold uppercase tracking-wider text-blue-200">Status saat ini</p>
                                <p class="mt-1 text-lg font-bold">{{ $activeStatusLabel }}</p>
                            </div>
                        </div>
                        @if (in_array($application->service_type, [\App\Models\ParticipantApplication::SERVICE_MAGANG_PKL, \App\Models\ParticipantApplication::SERVICE_WOPPS], true))
                            <div id="progress" class="scroll-mt-28 mt-8 border-t border-white/10 pt-6">
                                <div class="flex items-center justify-between text-xs font-bold text-blue-100">
                                    <span>{{ $application->service_type === \App\Models\ParticipantApplication::SERVICE_WOPPS ? 'Alur WOPPS' : 'Alur Magang / PKL / Kerja Praktik' }}</span>
                                    <span>Tahap {{ $activeProgressIndex + 1 }} dari {{ count($sidebarProgress) }}</span>
                                </div>
                                <ol class="mt-4 grid gap-3 sm:grid-cols-2 {{ $application->service_type === \App\Models\ParticipantApplication::SERVICE_WOPPS ? 'xl:grid-cols-3' : 'xl:grid-cols-6' }}">
                                    @foreach ($sidebarProgress as $index => $progressItem)
                                        <li class="rounded-2xl border p-3.5 {{ $progressItem['state'] === 'done' ? 'border-teal/50 bg-teal/15' : ($progressItem['state'] === 'current' ? 'border-white/60 bg-white/15 ring-2 ring-white/10' : 'border-white/10 bg-white/[0.04]') }}">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $progressItem['state'] === 'done' ? 'bg-teal text-white' : ($progressItem['state'] === 'current' ? 'bg-white text-ocean' : 'bg-white/10 text-blue-300') }}">
                                                @if ($progressItem['state'] === 'done')<i data-lucide="check" class="h-3.5 w-3.5"></i>@else{{ $index + 1 }}@endif
                                            </span>
                                            <span class="mt-3 block text-xs font-bold {{ $progressItem['state'] === 'upcoming' ? 'text-blue-300' : 'text-white' }}">{{ $progressItem['label'] }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        @else
                            <div id="progress" class="scroll-mt-28 mt-8">
                                <div class="flex items-center justify-between text-xs font-bold text-blue-100"><span>Tahap {{ $activeProgressIndex + 1 }} dari {{ count($sidebarProgress) }}</span><span>{{ $activeProgressLabel }}</span></div>
                                <div class="mt-2 h-3 overflow-hidden rounded-full bg-white/15"><div class="h-full rounded-full bg-teal" style="width: {{ (($activeProgressIndex + 1) / count($sidebarProgress)) * 100 }}%"></div></div>
                            </div>
                        @endif
                    </section>

                    @if ($application->service_type === \App\Models\ParticipantApplication::SERVICE_MAGANG_PKL)
                        @include('components.peserta.internship-workflow', [
                            'application' => $application,
                            'locations' => $internshipLocations,
                            'guestbookUrl' => $internshipGuestbookUrl,
                        ])
                    @else
                        @include('components.peserta.wopps-workflow', ['application' => $application])
                    @endif
                @endif
            </div>
        </div>
    </main>
@endsection
