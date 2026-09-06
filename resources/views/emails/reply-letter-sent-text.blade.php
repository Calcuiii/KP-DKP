SI-MELAYUR — Keputusan pengajuan

Halo {{ $participant->name }},

Dinas Kelautan dan Perikanan Provinsi Jawa Timur telah menetapkan keputusan resmi atas pengajuan Anda.

Status: {{ $accepted ? 'DITERIMA' : 'BELUM DAPAT DITERIMA' }}
@if($accepted)
@if($isWopps)
Petugas Dinas akan menghubungi Anda untuk tindak lanjut layanan WOPPS.
@else
Periode pelaksanaan: {{ $periodLabel }}
@endif
@endif

Surat balasan terlampir pada email ini dan juga tersedia pada dashboard portal peserta untuk diunduh kembali:
{{ $dashboardUrl }}

Jika Anda memiliki pertanyaan lebih lanjut mengenai isi surat balasan, silakan hubungi petugas Dinas secara langsung.
