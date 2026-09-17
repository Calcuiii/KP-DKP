@if ($application->completion_form_submitted_at)
    <div class="mt-5 rounded-xl bg-teal/10 p-4 text-sm">
        <p class="font-bold">Bukti pengisian form tersimpan</p>
        <p class="mt-2">Tahap 7 selesai. Pantau penerimaan sertifikat pada tahap 8.</p>
        <a href="{{ route('peserta.completion-proof.download', $application) }}" class="mt-2 inline-flex font-bold text-ocean underline">Unduh screenshot bukti</a>
    </div>
@endif
<form method="POST" action="{{ route('peserta.completion-proof.store', $application) }}" enctype="multipart/form-data" class="mt-6 space-y-4 border-t border-border pt-5">
    @csrf
    <h3 class="font-bold">{{ $application->completion_form_submitted_at ? 'Perbarui screenshot bukti' : 'Sudah mengisi Form Selesai Magang?' }}</h3>
    <p class="text-sm leading-7 text-muted-foreground">Unggah screenshot halaman konfirmasi Google Form yang menunjukkan jawaban Anda berhasil dikirim.</p>
    <div>
        <label for="completion_form_proof" class="block text-sm font-semibold">Screenshot bukti pengisian form</label>
        <div class="mt-2 rounded-xl border-2 border-dashed border-ocean/30 bg-ocean/[0.04] p-4 sm:p-5 focus-within:border-ocean">
            <input id="completion_form_proof" name="completion_form_proof" type="file" accept="image/jpeg,image/png" required aria-describedby="completion-proof-help" class="block w-full min-w-0 cursor-pointer rounded-lg border border-border bg-white p-2 text-sm text-navy file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-ocean file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-white hover:file:bg-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ocean">
            <p id="completion-proof-help" class="mt-3 text-xs text-muted-foreground">JPG atau PNG, maksimal 5 MB.</p>
        </div>
        @error('completion_form_proof')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <p class="text-xs leading-6 text-muted-foreground">Tahap 7 selesai setelah bukti tersimpan. Pastikan screenshot menunjukkan konfirmasi pengiriman berhasil. Penerbitan sertifikat tetap diproses oleh Dinas.</p>
    <button class="rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white">Simpan bukti pengisian</button>
</form>
