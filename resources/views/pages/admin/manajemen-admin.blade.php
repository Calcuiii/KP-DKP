@extends('layouts.admin')

@section('title', 'Manajemen Admin - DKP Assistant')

@section('content')
<div class="space-y-5">

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @error('email')
        <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700">
            {{ $message }}
        </div>
    @enderror

    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        <div class="flex items-center gap-3 border-b border-border px-5 py-4">
            <h3 class="flex-1 text-sm font-semibold text-navy">Daftar Administrator</h3>
            <button type="button" data-open-modal="add-admin-modal" class="flex items-center gap-1.5 rounded-xl bg-ocean px-3 py-2 text-xs font-semibold text-white">
                <i data-lucide="plus" class="h-3.5 w-3.5" aria-hidden="true"></i>
                Tambah Admin
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-[#F4F7FB] text-muted-foreground">
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Nama</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Email</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Role</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Login Terakhir</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Dibuat</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($admins as $admin)
                        <tr class="border-t border-border transition-colors hover:bg-[#F8FAFC]">
                            <td class="px-4 py-3 font-medium">{{ $admin->name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $admin->email }}</td>
                            <td class="px-4 py-3"><x-admin.status-badge :status="$admin->role_label" /></td>
                            <td class="px-4 py-3"><x-admin.status-badge :status="$admin->status" /></td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $admin->last_login_at?->format('Y-m-d H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $admin->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('admin.manajemen-admin.edit', $admin) }}" class="rounded-lg p-1.5 hover:bg-accent" title="Edit">
                                        <i data-lucide="edit-2" class="h-3 w-3" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.manajemen-admin.toggle-status', $admin) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg p-1.5 hover:bg-accent" title="Aktifkan/Nonaktifkan">
                                            <i data-lucide="lock" class="h-3 w-3" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.manajemen-admin.destroy', $admin) }}" onsubmit="return confirm('Hapus admin ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-red-500 hover:bg-accent" title="Hapus">
                                            <i data-lucide="trash-2" class="h-3 w-3" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Admin --}}
<div data-modal="add-admin-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between">
            <h3 class="text-base font-bold text-navy">Tambah Admin Baru</h3>
            <button type="button" data-close-modal="add-admin-modal" class="rounded-xl p-2 hover:bg-accent">
                <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.manajemen-admin.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="mb-1.5 block text-xs font-semibold">Nama Lengkap</label>
                <input name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Nama admin...">
                @error('name') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs" placeholder="admin@dkp.jatimprov.go.id">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold">Password</label>
                <input type="password" name="password" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs" placeholder="Minimal 8 karakter">
                @error('password') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold">Role</label>
                <select name="role" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs">
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                </select>
            </div>

            <button type="submit" class="w-full rounded-xl bg-ocean py-3 text-sm font-semibold text-white hover:opacity-90">
                Tambah Admin
            </button>
        </form>
    </div>
</div>
@endsection