@extends('layouts.admin')

@section('title', 'Knowledge Base - DKP Assistant')

@section('content')
<div class="space-y-5">

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    {{-- Metric Cards --}}
    <div class="grid gap-4 sm:grid-cols-4">
        <x-admin.metric-card icon="file-text" label="Total Dokumen" :value="$metrics['total']" color="ocean" />
        <x-admin.metric-card icon="check-circle" label="Dokumen Aktif" :value="$metrics['active']" color="teal" />
        <x-admin.metric-card icon="layers" label="Total Chunks" :value="$metrics['chunks']" color="indigo" />
        <x-admin.metric-card icon="x-circle" label="Gagal Diproses" :value="$metrics['failed']" color="red" />
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-4">
            <h3 class="flex-1 text-sm font-semibold text-navy">Daftar Dokumen</h3>

            <input
                type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari dokumen..."
                class="rounded-xl border border-border bg-input-background px-3 py-2 text-xs focus:outline-none"
            >

            <select name="category" class="rounded-xl border border-border bg-input-background px-3 py-2 text-xs" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach (['SOP', 'Panduan', 'FAQ', 'Template', 'Peraturan'] as $c)
                    <option value="{{ $c }}" @selected(request('category') === $c)>{{ $c }}</option>
                @endforeach
            </select>

            <select name="status" class="rounded-xl border border-border bg-input-background px-3 py-2 text-xs" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                @foreach (['Ready', 'Pending', 'Processing', 'Failed'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>

            <button type="submit" class="rounded-xl border border-border px-3 py-2 text-xs font-semibold hover:bg-accent">
                Cari
            </button>

            <button type="button" data-open-modal="upload-kb-modal" class="flex items-center gap-1.5 rounded-xl bg-ocean px-3 py-2 text-xs font-semibold text-white">
                <i data-lucide="plus" class="h-3.5 w-3.5" aria-hidden="true"></i>
                Tambah Dokumen
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-[#F4F7FB] text-muted-foreground">
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Nama Dokumen</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Kategori</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Tipe</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Versi</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Tgl Upload</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Status Indeks</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Chunks</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $doc)
                        <tr class="border-t border-border transition-colors hover:bg-[#F8FAFC]">
                            <td class="max-w-[180px] truncate px-4 py-3 font-medium">{{ $doc->name }}</td>
                            <td class="px-4 py-3"><x-admin.badge>{{ $doc->category }}</x-admin.badge></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $doc->type }}</td>
                            <td class="px-4 py-3 text-muted-foreground">v{{ $doc->version }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $doc->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3"><x-admin.status-badge :status="$doc->index_status" /></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $doc->chunks_count ?: '-' }}</td>
                            <td class="px-4 py-3"><x-admin.status-badge :status="$doc->status" /></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="rounded-lg p-1.5 hover:bg-accent" title="Lihat">
                                        <i data-lucide="eye" class="h-3 w-3" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.knowledge-base.reindex', $doc) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg p-1.5 hover:bg-accent" title="Proses Ulang">
                                            <i data-lucide="rotate-ccw" class="h-3 w-3" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.knowledge-base.destroy', $doc) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-red-500 hover:bg-accent" title="Hapus">
                                            <i data-lucide="trash-2" class="h-3 w-3" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-muted-foreground">
                                Belum ada dokumen. Klik "Tambah Dokumen" untuk mengunggah yang pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($documents->hasPages())
            <div class="border-t border-border px-5 py-3">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Upload Modal --}}
<div data-modal="upload-kb-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between">
            <h3 class="text-base font-bold text-navy">Upload Dokumen Knowledge Base</h3>
            <button type="button" data-close-modal="upload-kb-modal" class="rounded-xl p-2 hover:bg-accent">
                <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.knowledge-base.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <label class="block cursor-pointer rounded-2xl border-2 border-dashed border-border p-8 text-center transition-all hover:border-ocean hover:bg-[#F4F7FB]">
                <i data-lucide="upload" class="mx-auto mb-2 h-7 w-7 text-muted-foreground" aria-hidden="true"></i>
                <p class="mb-1 text-sm font-medium text-navy" data-file-label>Seret file ke sini atau klik untuk pilih</p>
                <p class="text-xs text-muted-foreground">Mendukung: PDF, DOCX, XLSX (maks. 50MB)</p>
                <input type="file" name="file" data-file-input class="hidden" accept=".pdf,.docx,.xlsx" required>
            </label>
            @error('file')
                <p class="text-xs text-destructive">{{ $message }}</p>
            @enderror

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold">Judul Dokumen</label>
                    <input name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Judul dokumen...">
                    @error('name') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold">Kategori</label>
                    <select name="category" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs">
                        @foreach (['SOP', 'Panduan', 'FAQ', 'Template', 'Peraturan'] as $c)
                            <option value="{{ $c }}" @selected(old('category') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold">Versi</label>
                    <input name="version" value="{{ old('version', '1.0') }}" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold">Tanggal Berlaku</label>
                    <input type="date" name="effective_date" value="{{ old('effective_date') }}" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full resize-none rounded-xl border border-border bg-input-background px-3 py-2 text-xs" placeholder="Deskripsi singkat dokumen...">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="w-full rounded-xl bg-ocean py-3 text-sm font-semibold text-white hover:opacity-90">
                Upload Dokumen
            </button>
        </form>
    </div>
</div>
@endsection