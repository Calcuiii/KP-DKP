<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('quota_status')->default('unknown');
            $table->unsignedInteger('display_order');
            $table->timestamp('quota_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('participant_application_documents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('participant_application_id');
            $table->string('type');
            $table->unsignedInteger('version')->default(1);
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->string('review_status')->default('submitted');
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['participant_application_id', 'type', 'version'], 'application_document_version_unique');
            $table->foreign('participant_application_id', 'application_document_application_fk')
                ->references('id')->on('participant_applications')->cascadeOnDelete();
        });

        Schema::table('participant_applications', function (Blueprint $table): void {
            $table->timestamp('guestbook_confirmed_at')->nullable()->after('status');
            $table->timestamp('letter_submitted_at')->nullable()->after('guestbook_confirmed_at');
            $table->timestamp('official_started_at')->nullable()->after('google_form_confirmed_at');
            $table->timestamp('official_ended_at')->nullable()->after('official_started_at');
            $table->string('decision')->nullable()->after('official_ended_at');
            $table->string('response_letter_path')->nullable()->after('decision');
        });

        $locations = [
            'Cabang Dinas Kelautan dan Perikanan Kab. Situbondo',
            'Cabang Dinas Kelautan dan Perikanan Kab. Malang',
            'Cabang Dinas Kelautan dan Perikanan Kab. Blitar',
            'Cabang Dinas Kelautan dan Perikanan Kab. Tuban',
            'UPT Pengujian Mutu dan Pengembangan Produk Kelautan dan Perikanan (PMP2KP) Surabaya',
            'UPT Pengujian Mutu dan Pengembangan Produk Kelautan dan Perikanan (PMP2KP) Banyuwangi',
            'UPT Budidaya Air Payau dan Laut Bangil',
            'Instalasi Budidaya Air Payau (IBAP) Probolinggo',
            'Instalasi Budidaya Air Payau (IBAP) Lamongan',
            'Instalasi Budidaya Air Payau (IBAP) Bangkalan',
            'Instalasi Budidaya Air Payau (IBAP) Banjar Kemuning Sidoarjo',
            'Instalasi Budidaya Air Payau (IBAP) Bangil',
            'Instalasi Budidaya Air Payau (IBAP) Raci Pasuruan',
            'Instalasi Budidaya Laut (IBL) Boncong Tuban',
            'Instalasi Budidaya Laut (IBL) Prigi Trenggalek',
            'Instalasi Budidaya Laut (IBL) Situbondo',
            'UPT Laboratorium Kesehatan Ikan dan Lingkungan di Pasuruan',
            'UPT Pelatihan Teknis Kelautan, Perikanan, Pesisir dan Pulau-Pulau Kecil di Probolinggo',
            'UPT Pelabuhan Perikanan Pantai (PPP) Mayangan Kota Probolinggo',
            'Instalasi Pelabuhan Perikanan Pantai (IPPP) Lekok Pasuruan',
            'Instalasi Pelabuhan Perikanan Pantai (IPPP) Paiton Probolinggo',
            'Instalasi Pelabuhan Perikanan Pantai (IPPP) Ngemplakrejo Kota Pasuruan',
        ];

        foreach ($locations as $index => $name) {
            DB::table('internship_locations')->insert([
                'name' => $name,
                'quota_status' => 'unknown',
                'display_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('participant_applications', function (Blueprint $table): void {
            $table->dropColumn(['guestbook_confirmed_at', 'letter_submitted_at', 'official_started_at', 'official_ended_at', 'decision', 'response_letter_path']);
        });

        Schema::dropIfExists('participant_application_documents');
        Schema::dropIfExists('internship_locations');
    }
};
