<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infographics', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('position')->unique();
            $table->string('type', 30);
            $table->unsignedTinyInteger('series_number')->nullable();
            $table->string('caption');
            $table->string('alt');
            $table->string('image_path');
            $table->unsignedInteger('image_width');
            $table->unsignedInteger('image_height');
            $table->timestamps();
        });

        $now = now();

        DB::table('infographics')->insert([
            [
                'position' => 1,
                'type' => 'infografis',
                'series_number' => 1,
                'caption' => 'Seri Infografis 1/07',
                'alt' => 'Alur Utama Magang dan Praktik Kerja Lapangan',
                'image_path' => 'images/infografis/infografis-01.jpg',
                'image_width' => 1587,
                'image_height' => 2245,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'position' => 2,
                'type' => 'infografis',
                'series_number' => 2,
                'caption' => 'Seri Infografis 2/07',
                'alt' => 'Prosedur Pelayanan Magang dan Praktik Kerja Lapangan',
                'image_path' => 'images/infografis/infografis-02.jpg',
                'image_width' => 1587,
                'image_height' => 2245,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'position' => 3,
                'type' => 'surat_resmi',
                'series_number' => null,
                'caption' => 'Surat Edaran Resmi',
                'alt' => 'Surat Edaran Pemerintah Provinsi Jawa Timur tentang Surat Keterangan Penelitian dan Praktik Kerja Lapangan',
                'image_path' => 'images/infografis/surat-edaran-resmi.jpeg',
                'image_width' => 526,
                'image_height' => 1035,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'position' => 4,
                'type' => 'infografis',
                'series_number' => 3,
                'caption' => 'Seri Infografis 3/07',
                'alt' => 'Surat Edaran Sekretaris Daerah tentang Magang, PKL, dan Penelitian',
                'image_path' => 'images/infografis/infografis-03.jpg',
                'image_width' => 1587,
                'image_height' => 2245,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'position' => 5,
                'type' => 'infografis',
                'series_number' => 4,
                'caption' => 'Seri Infografis 4/07',
                'alt' => 'Ketentuan Umum Peserta Magang dan Praktik Kerja Lapangan',
                'image_path' => 'images/infografis/infografis-04.jpg',
                'image_width' => 1587,
                'image_height' => 2245,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'position' => 6,
                'type' => 'infografis',
                'series_number' => 5,
                'caption' => 'Seri Infografis 5/07',
                'alt' => 'Informasi wajib dalam Surat Permohonan Magang dan PKL',
                'image_path' => 'images/infografis/infografis-05.jpg',
                'image_width' => 1587,
                'image_height' => 2245,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'position' => 7,
                'type' => 'infografis',
                'series_number' => 6,
                'caption' => 'Seri Infografis 6/07',
                'alt' => 'Penerbitan Surat Keterangan dan Sertifikat Magang dan PKL',
                'image_path' => 'images/infografis/infografis-06.jpg',
                'image_width' => 1587,
                'image_height' => 2245,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'position' => 8,
                'type' => 'infografis_wopps',
                'series_number' => 7,
                'caption' => 'Seri Infografis 7/07',
                'alt' => 'Persyaratan Dokumen Pengajuan Penelitian atau Permintaan Data',
                'image_path' => 'images/infografis/infografis-07.jpg',
                'image_width' => 1587,
                'image_height' => 2245,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('infographics');
    }
};
