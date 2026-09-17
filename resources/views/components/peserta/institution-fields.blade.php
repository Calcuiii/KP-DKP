<div>
    <label for="institution" class="mb-2 block text-sm font-semibold">Universitas / sekolah</label>
    <input id="institution" name="institution" value="{{ old('institution', $participant->institution ?? '') }}" required maxlength="255" autocomplete="organization" class="w-full rounded-xl border border-border px-4 py-3 text-sm" placeholder="Nama lengkap universitas atau sekolah">
    @error('institution')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
</div>
<div>
    <label for="phone" class="mb-2 block text-sm font-semibold">Nomor telepon</label>
    <input id="phone" name="phone" type="tel" value="{{ old('phone', $participant->phone ?? '') }}" required maxlength="25" autocomplete="tel" class="w-full rounded-xl border border-border px-4 py-3 text-sm" placeholder="08… atau +62…">
    @error('phone')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
</div>
<p class="text-xs leading-6 text-muted-foreground">Gunakan nama lengkap, email, dan universitas/sekolah yang sama saat mengisi formulir resmi Dinas.</p>
