@extends('layouts.admin')

@section('title', 'Infografis - DKP Assistant')

@section('content')
    <div class="space-y-5">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div>
            <h2 class="text-lg font-bold text-navy">Kelola Infografis</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Ganti gambar, caption, atau teks alternatif tanpa mengubah urutan seri yang tampil untuk publik.
            </p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($infographics as $infographic)
                <article class="overflow-hidden rounded-2xl border border-border bg-white shadow-sm">
                    <img
                        src="{{ $infographic->image_url }}"
                        alt="{{ $infographic->alt }}"
                        width="{{ $infographic->image_width }}"
                        height="{{ $infographic->image_height }}"
                        class="aspect-[3/4] w-full bg-secondary object-cover"
                    >

                    <div class="p-4">
                        <p class="text-xs font-semibold text-teal">Urutan {{ $infographic->position }}</p>
                        <h3 class="mt-1 text-sm font-bold text-navy">{{ $infographic->caption }}</h3>
                        <p class="mt-2 line-clamp-2 text-xs text-muted-foreground">{{ $infographic->alt }}</p>

                        <a
                            href="{{ route('admin.infographics.edit', $infographic) }}"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-ocean px-3 py-2 text-xs font-semibold text-white transition-opacity hover:opacity-90"
                        >
                            Edit Infografis
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endsection
