@extends('layouts.admin')

@section('title', 'Edit Admin - DKP Assistant')

@section('content')
<div class="mx-auto max-w-lg">
    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
        <h3 class="mb-5 text-sm font-semibold text-navy">Edit Data Admin</h3>

        <form method="POST" action="{{ route('admin.manajemen-admin.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1.5 block text-xs font-semibold">Nama Lengkap</label>
                <input name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs">
                @error('name') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs">
                @error('email') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold">Password Baru (opsional)</label>
                <input type="password" name="password" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs" placeholder="Kosongkan jika tidak ingin diubah">
                @error('password') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold">Role</label>
                <select name="role" class="w-full rounded-xl border border-border bg-input-background px-3 py-2 text-xs">
                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                    <option value="superadmin" @selected($user->role === 'superadmin')>Super Admin</option>
                </select>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('admin.manajemen-admin') }}" class="flex-1 rounded-xl border border-border py-2.5 text-center text-sm font-semibold hover:bg-accent">
                    Batal
                </a>
                <button type="submit" class="flex-1 rounded-xl bg-ocean py-2.5 text-sm font-semibold text-white hover:opacity-90">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection