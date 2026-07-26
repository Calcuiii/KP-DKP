@extends('layouts.admin')

@section('title', 'Edit Infografis - DKP Assistant')

@section('content')
    <div class="mx-auto max-w-3xl space-y-5">
        <div>
            <a href="{{ route('admin.infographics') }}" class="text-sm font-semibold text-ocean hover:underline">
                ← Kembali ke Kelola Infografis
            </a>

            <h2 class="mt-4 text-lg font-bold text-navy">Edit {{ $infographic->caption }}</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Urutan {{ $infographic->position }} bersifat tetap agar susunan galeri publik konsisten.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('admin.infographics.update', $infographic) }}"
            enctype="multipart/form-data"
            class="space-y-5 rounded-2xl border border-border bg-white p-5 shadow-sm"
        >
            @csrf
            @method('PUT')

            <img
                src="{{ $infographic->image_url }}"
                alt="{{ $infographic->alt }}"
                width="{{ $infographic->image_width }}"
                height="{{ $infographic->image_height }}"
                class="mx-auto max-h-96 max-w-full rounded-xl bg-secondary object-contain"
            >

            <div>
                <label for="image" class="mb-1.5 block text-xs font-semibold text-navy">Ganti Gambar</label>
                <input
                    id="image"
                    type="file"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="block w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs"
                >
                <p class="mt-1.5 text-xs text-muted-foreground">Opsional. JPG, JPEG, PNG, atau WEBP; maksimal 5MB.</p>
                @error('image')
                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="caption" class="mb-1.5 block text-xs font-semibold text-navy">Caption</label>
                <input
                    id="caption"
                    name="caption"
                    value="{{ old('caption', $infographic->caption) }}"
                    class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-sm"
                    required
                >
                @error('caption')
                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="alt" class="mb-1.5 block text-xs font-semibold text-navy">Deskripsi Gambar</label>
                <textarea
                    id="alt"
                    name="alt"
                    rows="3"
                    class="w-full resize-none rounded-xl border border-border bg-input-background px-3 py-2 text-sm"
                    required
                >{{ old('alt', $infographic->alt) }}</textarea>
                <p class="mt-1.5 text-xs text-muted-foreground">Dipakai sebagai teks aksesibel ketika gambar tidak dapat dilihat.</p>
                @error('alt')
                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-ocean px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                    Simpan Perubahan
                </button>

                <a href="{{ route('admin.infographics') }}" class="rounded-xl border border-border px-5 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
