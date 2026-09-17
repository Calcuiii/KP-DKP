<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('institution')->nullable();
            $table->string('phone', 25)->nullable();
        });
        Schema::table('participant_applications', function (Blueprint $table) {
            $table->date('presentation_date')->nullable();
            $table->string('presentation_photo_path')->nullable();
            $table->timestamp('presentation_submitted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('participant_applications', fn (Blueprint $table) => $table->dropColumn(['presentation_date', 'presentation_photo_path', 'presentation_submitted_at']));
        Schema::table('participants', fn (Blueprint $table) => $table->dropColumn(['institution', 'phone']));
    }
};
