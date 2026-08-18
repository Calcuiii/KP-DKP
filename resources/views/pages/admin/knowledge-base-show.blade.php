@extends('layouts.admin')

@section('title', 'Detail Dokumen - SI-MELAYUR')

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.knowledge-base') }}" class="text-xs font-medium text-ocean">← Kembali ke Knowledge Base</a>
            <h1 class="mt-2 text-xl font-bold text-navy">{{ $document->name }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">Detail dokumen dan teks yang digunakan untuk pengindeksan chatbot.</p>
        </div>

        <a href="{{ route('admin.knowledge-base.download', $document) }}" class="inline-flex items-center gap-2 rounded-xl bg-ocean px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90">
            <i data-lucide="download" class="h-4 w-4" aria-hidden="true"></i>
            Unduh File Asli
        </a>
    </div>

    <div class="grid gap-4 rounded-2xl border border-border bg-card p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <p class="text-xs text-muted-foreground">Kategori</p>
            <p class="mt-1 font-semibold text-navy">{{ $document->category }}</p>
        </div>
        <div>
            <p class="text-xs text-muted-foreground">Format</p>
            <p class="mt-1 font-semibold text-navy">{{ $document->type }}</p>
        </div>
        <div>
            <p class="text-xs text-muted-foreground">Versi</p>
            <p class="mt-1 font-semibold text-navy">v{{ $document->version }}</p>
        </div>
        <div>
            <p class="text-xs text-muted-foreground">Jumlah Bagian</p>
            <p class="mt-1 font-semibold text-navy">{{ $document->chunks_count }} chunk</p>
        </div>
        <div>
            <p class="text-xs text-muted-foreground">Status</p>
            <div class="mt-1"><x-admin.status-badge :status="$document->status" /></div>
        </div>
        <div>
            <p class="text-xs text-muted-foreground">Status Indeks</p>
            <div class="mt-1"><x-admin.status-badge :status="$document->index_status" /></div>
        </div>
        <div>
            <p class="text-xs text-muted-foreground">Tanggal Upload</p>
            <p class="mt-1 font-semibold text-navy">{{ $document->created_at->copy()->timezone('Asia/Jakarta')->format('Y-m-d H:i') }} WIB</p>
        </div>
        <div>
            <p class="text-xs text-muted-foreground">Tanggal Berlaku</p>
            <p class="mt-1 font-semibold text-navy">{{ $document->effective_date?->format('Y-m-d') ?? '-' }}</p>
        </div>
    </div>

    @if ($document->description)
        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-navy">Deskripsi</h2>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ $document->description }}</p>
        </div>
    @endif

    <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-navy">Teks Hasil Pemrosesan</h2>
        <p class="mt-1 text-xs text-muted-foreground">Teks ini digunakan sebagai dasar knowledge base chatbot.</p>

        @if ($processedContent)
            <pre class="mt-4 max-h-[60vh] overflow-auto whitespace-pre-wrap rounded-xl bg-[#F4F7FB] p-4 text-xs leading-relaxed text-navy">{{ $processedContent }}</pre>
        @else
            <p class="mt-4 rounded-xl border border-dashed border-border p-4 text-sm text-muted-foreground">Teks hasil pemrosesan belum tersedia. Silakan proses ulang dokumen ini.</p>
        @endif
    </div>
</div>
@endsection
