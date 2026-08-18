@php
    use App\Models\ParticipantApplicationDocument;

    $documentDefinitions = [
        ParticipantApplicationDocument::TYPE_WOPPS_IDENTITY => [
            'title' => 'Identitas Diri',
            'description' => 'KTM, KTP, atau SIM yang masih berlaku.',
            'format' => 'PDF, JPG, PNG',
            'icon' => 'id-card',
            'required' => true,
        ],

        ParticipantApplicationDocument::TYPE_WOPPS_REQUEST_LETTER => [
            'title' => 'Surat Permohonan',
            'description' => 'Surat resmi dari institusi pendidikan atau instansi asal.',
            'format' => 'PDF',
            'icon' => 'mail',
            'required' => true,
        ],

        ParticipantApplicationDocument::TYPE_WOPPS_PROPOSAL => [
            'title' => 'Proposal Kegiatan',
            'description' => 'Proposal penelitian, wawancara, observasi, atau kegiatan terkait.',
            'format' => 'PDF, DOC, DOCX',
            'icon' => 'file-text',
            'required' => true,
        ],

        ParticipantApplicationDocument::TYPE_WOPPS_ETHICS => [
            'title' => 'Persetujuan Etik',
            'description' => 'Unggah jika kegiatan Anda memang mempersyaratkannya.',
            'format' => 'PDF, DOC, DOCX',
            'icon' => 'shield-check',
            'required' => false,
        ],
    ];

    $documents = $application->documents
        ->filter(
            fn ($document) => str_starts_with($document->type, 'wopps_')
        )
        ->groupBy('type')
        ->map(
            fn ($items) => $items->sortByDesc('version')->first()
        );

    $requiredTypes = collect($documentDefinitions)
        ->filter(fn ($item) => $item['required'])
        ->keys();

    $uploadedRequired = $requiredTypes
        ->filter(fn ($type) => $documents->has($type))
        ->count();

    $isComplete =
        $application->status === 'wopps_documents_complete'
        || $application->google_form_confirmed_at !== null;

    $needsRevision =
        $application->status === 'wopps_revision_required';

    $stage = match ($application->status) {
        'wopps_documents_in_progress' => 2,
        'wopps_revision_required' => 2,
        'wopps_documents_complete' => 2,
        'wopps_response_pending' => 3,
        'wopps_accepted' => 4,
        'wopps_running' => 5,
        'wopps_report_pending' => 6,
        'wopps_certificate_pending' => 7,
        'wopps_completed' => 8,
        default => 1,
    };

    $stageLabels = [
        'Menunggu Verifikasi Administrasi',
        'Dokumen Sedang Ditinjau',
        'Menunggu Surat Balasan',
        'Pengajuan Diterima',
        'Pelaksanaan WOPPS',
        'Pengumpulan Laporan Akhir',
        'Sertifikat Diproses',
        'Selesai',
    ];

    /*
    |--------------------------------------------------------------------------
    | Riwayat aktivitas
    |--------------------------------------------------------------------------
    | Dibangun dari data yang memang sudah tersedia di project:
    | application + dokumen + konfirmasi Google Form.
    */

    $history = collect([
        [
            'date' => $application->created_at,
            'title' => 'Pengajuan WOPPS dibuat',
            'description' => 'Persiapan pengajuan WOPPS berhasil dibuat.',
            'icon' => 'file-plus-2',
        ],
    ]);

    foreach (
        $documents
            ->sortByDesc(fn ($document) => $document->created_at)
        as $document
    ) {
        $definition = $documentDefinitions[$document->type] ?? null;

        if (! $definition) {
            continue;
        }

        $history->push([
            'date' => $document->created_at,
            'title' => $definition['title'] . ' diunggah',
            'description' => $document->original_name,
            'icon' => 'upload',
        ]);
    }

    if ($application->google_form_confirmed_at) {
        $history->push([
            'date' => $application->google_form_confirmed_at,
            'title' => 'Google Form resmi dikonfirmasi',
            'description' => 'Peserta melanjutkan proses ke Google Form resmi WOPPS.',
            'icon' => 'external-link',
        ]);
    }

    $history = $history
        ->sortByDesc('date')
        ->values();
@endphp

<section id="wopps" class="space-y-6">

    {{-- HEADER --}}
    <section class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy via-[#123d72] to-teal p-7 text-white shadow-xl sm:p-9">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">

            <div class="max-w-3xl">

                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold">
                    <i data-lucide="microscope" class="h-4 w-4"></i>
                    WOPPS
                </span>

                <h2 class="mt-4 text-2xl font-extrabold tracking-tight sm:text-3xl">
                    Wawancara, Observasi, Penelitian & Sejenisnya
                </h2>

                <p class="mt-3 text-sm leading-relaxed text-blue-100 sm:text-base">
                    Siapkan dokumen Anda sebelum melanjutkan ke Google Form resmi
                    Dinas Kelautan dan Perikanan Provinsi Jawa Timur.
                </p>

            </div>

            <div class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4">

                <p class="text-[11px] font-bold uppercase tracking-wider text-blue-200">
                    Progress
                </p>

                <p class="mt-1 text-lg font-extrabold">
                    Tahap {{ $stage }} dari 8
                </p>

                <p class="text-xs text-blue-100">
                    {{ $stageLabels[$stage - 1] }}
                </p>

            </div>

        </div>

        <div class="mt-8 grid grid-cols-8 gap-1.5">
            @for ($i = 1; $i <= 8; $i++)
                <span
                    class="h-2 rounded-full {{ $i <= $stage ? 'bg-teal' : 'bg-white/15' }}"
                ></span>
            @endfor
        </div>

    </section>

    {{-- UPLOAD DOKUMEN --}}
    <section
        id="upload-dokumen"
        class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8"
    >

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">
                    Tahap 1
                </p>

                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-navy">
                    Upload Dokumen WOPPS
                </h2>

                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                    Unggah dokumen wajib untuk mempersiapkan pengajuan WOPPS.
                </p>

            </div>

            <span class="inline-flex w-fit rounded-full bg-teal/10 px-4 py-2 text-xs font-bold text-teal">
                {{ $uploadedRequired }}/3 dokumen wajib
            </span>

        </div>

        <div class="mt-7 grid gap-4 lg:grid-cols-2">

            @foreach ($documentDefinitions as $type => $definition)

                @php
                    $document = $documents->get($type);

                    $revision =
                        $document?->review_status
                        === ParticipantApplicationDocument::REVIEW_REVISION;
                @endphp

                <article
                    class="rounded-3xl border p-5
                    {{ $revision
                        ? 'border-amber-200 bg-amber-50/40'
                        : ($document
                            ? 'border-teal/20 bg-teal/[0.03]'
                            : 'border-border bg-background') }}"
                >

                    <div class="flex items-start gap-4">

                        <span
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl
                            {{ $revision
                                ? 'bg-amber-100 text-amber-700'
                                : ($document
                                    ? 'bg-teal/10 text-teal'
                                    : 'bg-ocean/10 text-ocean') }}"
                        >
                            <i
                                data-lucide="{{
                                    $revision
                                        ? 'triangle-alert'
                                        : ($document
                                            ? 'circle-check'
                                            : $definition['icon'])
                                }}"
                                class="h-5 w-5"
                            ></i>
                        </span>

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <h3 class="text-sm font-extrabold text-navy">
                                    {{ $definition['title'] }}
                                </h3>

                                @if ($definition['required'])

                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700">
                                        Wajib
                                    </span>

                                @else

                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-600">
                                        Opsional
                                    </span>

                                @endif

                            </div>

                            <p class="mt-1 text-xs leading-relaxed text-muted-foreground">
                                {{ $definition['description'] }}
                            </p>

                            @if ($document)

                                <div class="mt-3 flex items-center gap-2">

                                    <span class="min-w-0 flex-1 truncate rounded-xl bg-white px-3 py-2 text-xs font-semibold text-navy shadow-sm">
                                        {{ $document->original_name }}
                                    </span>

                                    <a
                                        href="{{ route('peserta.document.download', $document) }}"
                                        class="shrink-0 text-xs font-bold text-ocean hover:text-navy"
                                    >
                                        Unduh
                                    </a>

                                </div>

                            @endif

                            @if ($revision)

                                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-relaxed text-amber-800">

                                    <strong>Perlu diperbaiki.</strong>

                                    {{ $document->review_notes ?: 'Silakan unggah versi dokumen terbaru.' }}

                                </div>

                            @endif

                        </div>

                    </div>

                    <form
                        method="POST"
                        action="{{ route('peserta.wopps.document.store') }}"
                        enctype="multipart/form-data"
                        class="mt-5"
                    >

                        @csrf

                        <input
                            type="hidden"
                            name="type"
                            value="{{ $type }}"
                        >

                        <label class="block cursor-pointer rounded-2xl border border-dashed border-ocean/30 bg-ocean/[0.03] p-4 transition hover:border-ocean hover:bg-ocean/[0.06]">

                            <div class="flex items-center gap-3">

                                <i
                                    data-lucide="upload-cloud"
                                    class="h-5 w-5 text-ocean"
                                ></i>

                                <div>

                                    <p class="text-xs font-bold text-navy">
                                        {{ $document ? 'Ganti Dokumen' : 'Pilih Dokumen' }}
                                    </p>

                                    <p class="mt-0.5 text-[10px] text-muted-foreground">
                                        {{ $definition['format'] }} · Maks. 10 MB
                                    </p>

                                </div>

                            </div>

                            <input
                                type="file"
                                name="document"
                                required
                                class="mt-3 block w-full text-xs"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            >

                        </label>

                        <button
                            type="submit"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-ocean px-5 py-3 text-sm font-bold text-white transition hover:brightness-110"
                        >
                            {{ $document ? 'Unggah Versi Baru' : 'Unggah Dokumen' }}

                            <i
                                data-lucide="arrow-up"
                                class="h-4 w-4"
                            ></i>
                        </button>

                    </form>

                </article>

            @endforeach

        </div>

    </section>

    {{-- CEK KELENGKAPAN --}}
    <section
        id="cek-kelengkapan"
        class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8"
    >

        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">
                    Tahap 2
                </p>

                <h2 class="mt-2 text-2xl font-extrabold text-navy">
                    Cek Kelengkapan Dokumen
                </h2>

                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground">
                    Pastikan semua dokumen wajib sudah tersedia sebelum melanjutkan
                    ke Google Form resmi.
                </p>

            </div>

            <form
                method="POST"
                action="{{ route('peserta.wopps.completeness.check') }}"
            >

                @csrf

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-ocean px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-ocean/15 transition hover:brightness-110"
                >
                    <i data-lucide="scan-search" class="h-4 w-4"></i>
                    Cek Kelengkapan Dokumen
                </button>

            </form>

        </div>

        @if ($needsRevision)

            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">

                <div class="flex items-start gap-4">

                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <i data-lucide="triangle-alert" class="h-5 w-5"></i>
                    </span>

                    <div>

                        <h3 class="text-sm font-extrabold text-amber-900">
                            Dokumen masih perlu dilengkapi
                        </h3>

                        <p class="mt-1 text-sm leading-relaxed text-amber-800">
                            Periksa bagian upload dan lengkapi dokumen yang masih diperlukan.
                        </p>

                        <a
                            href="#upload-dokumen"
                            class="mt-3 inline-flex items-center gap-2 text-xs font-extrabold text-amber-900 underline"
                        >
                            Revisi & Unggah Ulang

                            <i
                                data-lucide="arrow-up-right"
                                class="h-3.5 w-3.5"
                            ></i>
                        </a>

                    </div>

                </div>

            </div>

        @elseif ($isComplete)

            <div class="mt-6 rounded-2xl border border-teal/20 bg-teal/[0.05] p-5">

                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-start gap-4">

                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal text-white">
                            <i data-lucide="circle-check" class="h-6 w-6"></i>
                        </span>

                        <div>

                            <h3 class="text-sm font-extrabold text-teal">
                                Dokumen Anda sudah lengkap
                            </h3>

                            <p class="mt-1 text-sm leading-relaxed text-muted-foreground">
                                Semua dokumen wajib persiapan WOPPS sudah tersedia.
                            </p>

                        </div>

                    </div>

                    @if (! $application->google_form_confirmed_at)

                        <form
                            method="POST"
                            action="{{ route('peserta.wopps.google-form') }}"
                        >

                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-teal px-5 py-3.5 text-sm font-bold text-white transition hover:brightness-110 sm:w-auto"
                            >
                                Lanjut ke Google Form Resmi WOPPS

                                <i
                                    data-lucide="external-link"
                                    class="h-4 w-4"
                                ></i>
                            </button>

                        </form>

                    @else

                        <span class="rounded-full bg-teal/10 px-4 py-2 text-xs font-bold text-teal">
                            Google Form sudah dikonfirmasi
                        </span>

                    @endif

                </div>

                <p class="mt-4 text-xs leading-relaxed text-muted-foreground">
                    Pengajuan final dilakukan melalui Google Form eksternal resmi DKP.
                    Portal ini berfungsi sebagai pendamping persiapan.
                </p>

            </div>

        @endif

    </section>

    {{-- MONITORING --}}
    <section
        id="progress"
        class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8"
    >

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">
            Monitoring Pengajuan
        </p>

        <h2 class="mt-2 text-2xl font-extrabold text-navy">
            Status Pengajuan WOPPS
        </h2>

        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
            Pantau perjalanan pengajuan Anda melalui delapan tahap.
        </p>

        <div class="mt-7 grid gap-3 md:grid-cols-2 xl:grid-cols-4">

            @foreach ($stageLabels as $index => $label)

                @php
                    $number = $index + 1;
                    $done = $number < $stage;
                    $active = $number === $stage;
                @endphp

                <article
                    class="rounded-2xl border p-4
                    {{ $active
                        ? 'border-ocean bg-ocean/[0.04]'
                        : ($done
                            ? 'border-teal/20 bg-teal/[0.04]'
                            : 'border-border bg-background') }}"
                >

                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-extrabold
                        {{ $done
                            ? 'bg-teal text-white'
                            : ($active
                                ? 'bg-ocean text-white'
                                : 'bg-muted text-muted-foreground') }}"
                    >

                        @if ($done)

                            <i data-lucide="check" class="h-4 w-4"></i>

                        @else

                            {{ $number }}

                        @endif

                    </span>

                    <h3 class="mt-4 text-sm font-extrabold text-navy">
                        {{ $label }}
                    </h3>

                    @if ($active)

                        <p class="mt-1 text-xs font-semibold text-ocean">
                            Tahap saat ini
                        </p>

                    @elseif ($done)

                        <p class="mt-1 text-xs text-teal">
                            Tahap selesai
                        </p>

                    @else

                        <p class="mt-1 text-xs text-muted-foreground">
                            Belum dimulai
                        </p>

                    @endif

                </article>

            @endforeach

        </div>

    </section>

    {{-- RIWAYAT --}}
    <section
        id="riwayat"
        class="rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8"
    >

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal">
            Aktivitas
        </p>

        <h2 class="mt-2 text-2xl font-extrabold text-navy">
            Riwayat Aktivitas
        </h2>

        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
            Aktivitas pengajuan WOPPS terbaru.
        </p>

        <div class="mt-7 space-y-4">

            @foreach ($history as $item)

                <article class="flex gap-4">

                    <div class="flex flex-col items-center">

                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ocean/10 text-ocean">
                            <i
                                data-lucide="{{ $item['icon'] }}"
                                class="h-4 w-4"
                            ></i>
                        </span>

                        @if (! $loop->last)

                            <span class="mt-2 h-full w-px bg-border"></span>

                        @endif

                    </div>

                    <div class="min-w-0 flex-1 rounded-2xl border border-border bg-background p-4">

                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                            <h3 class="text-sm font-extrabold text-navy">
                                {{ $item['title'] }}
                            </h3>

                            <time class="text-[11px] font-semibold text-muted-foreground">
                                {{ $item['date']?->format('d M Y, H:i') }}
                            </time>

                        </div>

                        <p class="mt-1 text-xs leading-relaxed text-muted-foreground">
                            {{ $item['description'] }}
                        </p>

                    </div>

                </article>

            @endforeach

        </div>

    </section>

    {{-- CHATBOT --}}
    <section class="rounded-[2rem] border border-border bg-background p-6 sm:p-8">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-start gap-4">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-teal/10 text-teal">
                    <i data-lucide="message-circle" class="h-5 w-5"></i>
                </span>

                <div>

                    <h2 class="text-sm font-extrabold text-navy">
                        Butuh bantuan tentang WOPPS?
                    </h2>

                    <p class="mt-1 text-xs leading-relaxed text-muted-foreground">
                        Tanya Asisten Si-Molek untuk mendapatkan informasi resmi DKP.
                    </p>

                </div>

            </div>

            <a
                href="{{ route('chatbot') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-ocean/30 bg-white px-5 py-3 text-xs font-bold text-ocean transition hover:bg-ocean hover:text-white"
            >
                Tanya Asisten Chatbot

                <i
                    data-lucide="arrow-up-right"
                    class="h-4 w-4"
                ></i>
            </a>

        </div>

    </section>

</section>