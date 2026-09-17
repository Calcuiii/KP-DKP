<section id="penyelesaian" class="scroll-mt-24 rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8">
    <p class="text-xs font-bold uppercase tracking-widest text-teal">Tahap 6 · Pelaksanaan, laporan &amp; presentasi</p>
    <h2 class="mt-2 text-2xl font-bold">Selesaikan magang Anda</h2>
    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div><h3 class="text-lg font-bold">Laporan kegiatan</h3><p class="mt-2 text-sm leading-7 text-muted-foreground">Susun laporan selama magang menggunakan format sekolah atau perguruan tinggi Anda. Siapkan versi PDF dan koordinasikan isinya dengan pembimbing.</p></div>
        <div><h3 class="text-lg font-bold">Presentasi hasil</h3><p class="mt-2 text-sm leading-7 text-muted-foreground">Siapkan bahan paparan hasil kegiatan. Presentasi minggu terakhir bersifat tentatif; koordinasikan jadwalnya dengan pembimbing di lokasi magang.</p></div>
    </div>
    <a href="{{ route('peserta.activities') }}" class="mt-5 inline-flex text-sm font-bold text-ocean underline underline-offset-4">Lihat jadwal kegiatan</a>
    @include('components.peserta.presentation-form')
</section>

<section id="form-selesai" class="relative scroll-mt-24 rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8 {{ $application->presentation_submitted_at ? '' : 'opacity-60' }}">
    @unless ($application->presentation_submitted_at)
        <span class="absolute right-6 top-6 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500" aria-label="Tahap terkunci"><i data-lucide="lock" class="h-4 w-4" aria-hidden="true"></i></span>
    @endunless
    <p class="text-xs font-bold uppercase tracking-widest text-teal">Tahap 7 · Setelah kegiatan selesai</p>
    <h2 class="mt-2 text-2xl font-bold">Form selesai magang</h2>
    @if (! $application->presentation_submitted_at)
        <div class="mt-6 rounded-2xl border border-border bg-background p-5">
            <p class="text-sm font-bold text-muted-foreground">Tahap belum tersedia</p>
            <p class="mt-2 text-sm leading-7 text-muted-foreground">Tahap 7 terkunci. Simpan tanggal presentasi dan foto bukti pada tahap 6 terlebih dahulu.</p>
        </div>
    @else
    <p class="mt-3 text-sm leading-7 text-muted-foreground">Pilih jenjang pendidikan Anda untuk melihat berkas yang perlu disiapkan. Pastikan pengiriman berhasil pada Google Form.</p>
    <div class="mt-5 divide-y divide-border">
        @foreach ([
            ['SMA/SMK', 'Sekolah', 'magang/PKL', 'https://bit.ly/SelesaiMagangPKL-SM'],
            ['Perguruan Tinggi', 'Fakultas', 'magang/PKL/penelitian', 'https://bit.ly/SelesaiMagangPKL-PT'],
        ] as [$level, $issuer, $activity, $url])
            <details class="py-5" name="completion-education">
                <summary class="cursor-pointer text-base font-bold text-navy">{{ $level }} — Berkas yang perlu disiapkan</summary>
                <ul class="mt-4 list-disc space-y-3 pl-5 text-sm leading-7 text-muted-foreground">
                    <li>Surat Permohonan Penerbitan Surat Keterangan dan Sertifikat {{ $issuer === 'Sekolah' ? '(dari Sekolah)' : '(Resmi) yang dikeluarkan Fakultas' }}, yang telah disiapkan sejak awal.</li>
                    <li>File presentasi paparan hasil kegiatan {{ $activity }}.</li>
                    <li>Foto atau dokumentasi selama kegiatan {{ $activity }}.</li>
                    <li>Foto atau dokumentasi saat paparan presentasi hasil kegiatan magang/PKL/penelitian.</li>
                    <li>Laporan kegiatan {{ $activity }}.</li>
                </ul>
                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white">Buka Form {{ $level }}</a>
            </details>
        @endforeach
    </div>
    <p class="mt-4 border-t border-border pt-4 text-sm leading-7 text-muted-foreground">Gunakan surat yang telah disiapkan sejak awal. Permohonan sertifikat tidak dapat diajukan setelah magang selesai. DKP tidak lagi menerbitkan Surat Keterangan. Ikuti ketentuan format, ukuran, dan jumlah berkas pada Google Form.</p>
    @include('components.peserta.completion-form-proof')
    @endif
</section>

@php($certificateStageUnlocked = $application->presentation_submitted_at && $application->completion_form_submitted_at)
<section id="penerimaan-sertifikat" class="relative scroll-mt-24 rounded-[2rem] border border-border bg-white p-6 shadow-sm sm:p-8 {{ $certificateStageUnlocked ? '' : 'opacity-60' }}">
    @unless ($certificateStageUnlocked)
        <span class="absolute right-6 top-6 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500" aria-label="Tahap terkunci"><i data-lucide="lock" class="h-4 w-4" aria-hidden="true"></i></span>
    @endunless
    <p class="text-xs font-bold uppercase tracking-widest text-teal">Tahap 8 · Sesuai pengajuan awal</p>
    <h2 class="mt-2 text-2xl font-bold">Penerimaan sertifikat</h2>
    <p class="mt-3 max-w-3xl text-sm leading-7 text-muted-foreground">Penerbitan sertifikat mengikuti permohonan yang telah disampaikan sejak awal dan proses Dinas. Periksa identitas pada sertifikat yang diterima dan simpan untuk arsip pribadi.</p>
    @if (! $certificateStageUnlocked)
        <div class="mt-6 rounded-2xl border border-border bg-background p-5">
            <p class="text-sm font-bold text-muted-foreground">Tahap belum tersedia</p>
            <p class="mt-2 text-sm leading-7 text-muted-foreground">Tahap 8 terkunci sampai tahap 6 selesai dan screenshot bukti pengisian Form Selesai Magang pada tahap 7 tersimpan.</p>
        </div>
    @elseif ($application->certificate_path)
        <p class="mt-4 text-sm font-semibold text-teal">Sertifikat telah diterbitkan oleh Dinas dan tersedia di portal.</p>
        <a href="{{ route('peserta.certificate.download', $application) }}" class="mt-4 inline-flex rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white">Unduh sertifikat PDF</a>
    @else
        <p class="mt-4 rounded-xl bg-light p-4 text-sm leading-7 text-muted-foreground">Sertifikat belum diterbitkan di portal. Setelah admin mengirimkannya, sertifikat dapat diunduh di sini dan dikirimkan juga melalui email Anda.</p>
    @endif
</section>
