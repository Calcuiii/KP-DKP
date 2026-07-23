@props(['status'])

@php
$map = [
    'Dijawab' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Dijawab'],
    'Tidak Ditemukan' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Tidak Ditemukan'],
    'Positif' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Positif'],
    'Negatif' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Negatif'],
    'Baru' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Baru'],
    'Ditinjau' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Ditinjau'],
    'Perlu Update KB' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Perlu Update KB'],
    'Selesai' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Selesai'],
    'Admin' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Admin'],
    'Super Admin' => ['bg' => 'bg-teal-100', 'text' => 'text-teal-700', 'label' => 'Super Admin'],
    'Aktif' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Aktif'],
    'Nonaktif' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Nonaktif'],
    'Upload' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Upload'],
    'Edit' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Edit'],
    'Delete' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Delete'],
    'Login' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Login'],
    'Tambah Admin' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Tambah Admin'],
];
$conf = $map[$status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => $status];
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium {{ $conf['bg'] }} {{ $conf['text'] }}">
    {{ $conf['label'] }}
</span>