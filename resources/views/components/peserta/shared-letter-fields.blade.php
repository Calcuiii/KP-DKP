<div class="my-4 rounded-xl border border-border bg-background p-4 text-sm">
    <p class="font-semibold text-navy">Surat untuk beberapa peserta</p>
    <p class="mt-2 text-muted-foreground">Jika surat mencakup beberapa peserta, unggah surat beserta lampiran yang mencantumkan nama Anda dalam satu PDF. Setiap peserta tetap mengunggah melalui akun sendiri. File yang sama boleh digunakan peserta lain.</p>
    <label class="mt-3 flex items-start gap-2"><input type="checkbox" name="shared_letter" value="1" @checked(old('shared_letter'))> Surat ini digunakan bersama peserta lain.</label>
    <p class="mt-2 text-xs text-muted-foreground">Untuk surat bersama, isi institusi penerbit sesuai dokumen. Pengajuan dikelompokkan jika institusi dan file suratnya sama. Gunakan salinan PDF yang sama, bukan hasil pindai ulang.</p>
    <label class="mt-3 block">Institusi penerbit surat
        <input name="letter_institution" maxlength="255" value="{{ old('letter_institution') }}" class="mt-1 block w-full rounded-lg border border-border p-2">
    </label>
    @error('letter_institution')<p class="text-destructive">{{ $message }}</p>@enderror

    <p class="mt-3 text-xs text-muted-foreground">Pastikan nama dan NIM/NIS Anda tercantum. Keputusan dan periode kegiatan berlaku untuk pengajuan Anda sendiri.</p>
</div>
