<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_applications', function (Blueprint $table) {
            $table->string('certificate_path')->nullable();
            $table->timestamp('certificate_published_at')->nullable();
            $table->timestamp('certificate_emailed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('participant_applications', function (Blueprint $table) {
            $table->dropColumn(['certificate_path', 'certificate_published_at', 'certificate_emailed_at']);
        });
    }
};
