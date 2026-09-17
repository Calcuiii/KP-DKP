@if ($application->presentation_submitted_at)
    <div class="mt-5 rounded-xl bg-teal/10 p-4 text-sm">
        <p class="font-bold">Bukti presentasi tersimpan</p>
        <p class="mt-2">Tanggal presentasi: {{ $application->presentation_date->translatedFormat('d F Y') }}. Tahap 7 terbuka.</p>
        <a href="{{ route('peserta.presentation.download', $application) }}" class="mt-2 inline-flex font-bold text-ocean underline">Unduh foto bukti</a>
    </div>
@endif
<form method="POST" action="{{ route('peserta.presentation.store', $application) }}" enctype="multipart/form-data" class="mt-6 space-y-4 border-t border-border pt-5">
    @csrf
    <h3 class="font-bold">{{ $application->presentation_submitted_at ? 'Perbarui bukti presentasi' : 'Catat pelaksanaan presentasi' }}</h3>
    <div><label for="presentation_date" class="block text-sm font-semibold">Tanggal presentasi</label><input id="presentation_date" type="date" name="presentation_date" required max="{{ now()->format('Y-m-d') }}" value="{{ old('presentation_date', $application->presentation_date?->format('Y-m-d')) }}" class="mt-2 rounded-xl border border-border p-3">@error('presentation_date')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    <div>
        <label for="presentation_photo" class="block text-sm font-semibold">Foto bukti presentasi</label>
        <div class="mt-2 rounded-xl border-2 border-dashed border-ocean/30 bg-ocean/[0.04] p-4 sm:p-5 focus-within:border-ocean">
            <p id="presentation-photo-help" class="mb-3 text-sm text-navy">Pilih foto dokumentasi saat Anda melakukan presentasi.</p>
            <input id="presentation_photo" type="file" name="presentation_photo" accept="image/jpeg,image/png" required aria-describedby="presentation-photo-help presentation-photo-format{{ $errors->has('presentation_photo') ? ' presentation-photo-error' : '' }}" @if ($errors->has('presentation_photo')) aria-invalid="true" @endif class="block w-full min-w-0 cursor-pointer rounded-lg border border-border bg-white p-2 text-sm text-navy file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-ocean file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-white hover:file:bg-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ocean">
            <p id="presentation-photo-format" class="mt-3 text-xs text-muted-foreground">JPG atau PNG, maksimal 5 MB. Setelah memilih foto, klik Simpan bukti presentasi.</p>
        </div>
        @error('presentation_photo')<p id="presentation-photo-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <p class="text-xs leading-6 text-muted-foreground">Data ini merupakan laporan peserta dan tidak melalui pemeriksaan admin. Tahap 7 terbuka setelah tanggal dan foto bukti tersimpan.</p>
    <button class="rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white">Simpan bukti presentasi</button>
</form>
