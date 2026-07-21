<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $admins = User::whereIn('role', ['admin', 'superadmin'])
            ->latest()
            ->get();

        return view('pages.admin.manajemen-admin', compact('admins'));
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'Aktif',
        ]);

        \App\Models\ActivityLog::record('Tambah Admin', 'Manajemen Admin', "Menambahkan admin baru \"{$request->name}\"");

        return redirect()->route('admin.manajemen-admin')
            ->with('status', 'Admin baru berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('pages.admin.manajemen-admin-edit', compact('user'));
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        \App\Models\ActivityLog::record('Edit', 'Manajemen Admin', "Memperbarui data admin \"{$user->name}\"");

        return redirect()->route('admin.manajemen-admin')
            ->with('status', 'Data admin berhasil diperbarui.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('status', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        $user->update([
            'status' => $user->status === 'Aktif' ? 'Nonaktif' : 'Aktif',
        ]);

        return back()->with('status', 'Status admin berhasil diubah.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('status', 'Anda tidak bisa menghapus akun sendiri.');
        }

        \App\Models\ActivityLog::record('Delete', 'Manajemen Admin', "Menghapus admin \"{$user->name}\"");

        $user->delete();

        return redirect()->route('admin.manajemen-admin')
            ->with('status', 'Admin berhasil dihapus.');
    }
}