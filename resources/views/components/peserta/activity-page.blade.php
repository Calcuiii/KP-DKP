@php
    $officialStarted = $application->official_started_at;
    $officialEnded = $application->official_ended_at;
    $today = now()->startOfDay();
    $preparationReminderDate = $officialEnded?->copy()->subDays(10)->startOfDay();
    $isPreparationWindow = $preparationReminderDate && $today->betweenIncluded($preparationReminderDate, $officialEnded->copy()->startOfDay());
@endphp

<section id="kegiatan" class="space-y-6">
    <header class="px-1 py-3">
        <p class="text-xs font-bold uppercase tracking-widest text-teal">Kegiatan magang</p>
        <h1 class="mt-2 text-3xl font-bold">Hari yang baik untuk belajar.</h1>
        <p class="mt-2 text-sm text-muted-foreground">Panduan dan agenda magang Anda, dalam satu tempat.</p>
    </header>

    <div class="grid items-start gap-5 xl:grid-cols-[20rem_minmax(0,1fr)]">
        <div class="min-w-0 xl:col-start-2 xl:row-start-1">
<section id="ketentuan-peserta" class="scroll-mt-24 rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
    <h2 class="text-xl font-bold">Ketentuan &amp; tata tertib peserta</h2>
    <p class="mt-2 text-sm leading-7 text-muted-foreground">Panduan selama magang di DKP Jawa Timur.</p>

    <div class="mt-6 space-y-6 text-sm leading-7 text-navy">
        <div>
            <h3 class="font-bold">Jam kegiatan</h3>
            <dl class="mt-2">
                <div class="flex flex-wrap justify-between gap-x-4"><dt>Senin – Kamis</dt><dd>07.30 – 16.00 WIB</dd></div>
                <div class="flex flex-wrap justify-between gap-x-4"><dt>Jumat</dt><dd>07.00 – 16.30 WIB</dd></div>
            </dl>
        </div>
        <div class="border-t border-border pt-5">
            <h3 class="font-bold">Kehadiran dan etika</h3>
            <ul class="mt-2 list-disc space-y-2 pl-5">
                <li>Ikuti seluruh kegiatan, termasuk upacara dan kegiatan insidentil.</li>
                <li>Patuhi aturan kantor dan jaga tata krama kepada pegawai serta rekan kerja.</li>
                <li>Jaga keamanan dan kerahasiaan dokumen negara.</li>
            </ul>
        </div>
        <div class="border-t border-border pt-5">
            <h3 class="font-bold">Laporan dan presentasi</h3>
            <p class="mt-2">Gunakan format laporan dari institusi asal dalam bentuk PDF. Koordinasikan presentasi minggu terakhir dengan pembimbing; jadwalnya bersifat tentatif.</p>
        </div>
        <div class="border-t border-border pt-5">
            <h3 class="font-bold text-red-800">Sanksi pelanggaran</h3>
            <ul class="mt-2 list-disc space-y-2 pl-5">
                <li><strong>Ringan:</strong> pembinaan atau teguran lisan.</li>
                <li><strong>Sedang:</strong> penundaan sertifikat.</li>
                <li><strong>Berat:</strong> penghentian kegiatan tanpa memperoleh sertifikat.</li>
            </ul>
        </div>
    </div>
    <p class="mt-6 border-t border-border pt-4 text-xs leading-6 text-muted-foreground">Sumber: Ketentuan Umum Peserta Magang dan PKL. Ikuti arahan resmi terbaru dari Dinas atau pembimbing.</p>
</section>
        </div>
        <aside id="kalender-kegiatan" class="w-full max-w-sm scroll-mt-24 rounded-2xl border border-border bg-white p-5 shadow-sm xl:col-start-1 xl:row-start-1 xl:max-w-none" aria-label="Kalender kegiatan magang">
            <h2 class="text-lg font-bold">Agenda saya</h2>
            <p class="sr-only">Kalender kegiatan magang</p>
            @if ($officialStarted && $officialEnded)
                <div class="mt-5">
                    <x-peserta.internship-calendar compact :official-started="$officialStarted" :official-ended="$officialEnded" :today="$today" :preparation-reminder-date="$preparationReminderDate" :is-preparation-window="$isPreparationWindow" />
                </div>
                <dl class="mt-5 space-y-3 border-t border-border pt-4 text-xs">
                    <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Mulai magang</dt><dd class="font-bold">{{ $officialStarted->translatedFormat('d M Y') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Akhir magang</dt><dd class="font-bold">{{ $officialEnded->translatedFormat('d M Y') }}</dd></div>
                </dl>
            @else
                <p class="mt-5 text-sm text-muted-foreground">Menunggu admin menetapkan tanggal mulai dan selesai resmi.</p>
            @endif
            <p class="mt-4 text-xs leading-relaxed text-muted-foreground">Jadwal presentasi mengikuti arahan pembimbing.</p>
        </aside>
    </div>
</section>
