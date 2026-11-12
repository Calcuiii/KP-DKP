<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_applications', function (Blueprint $table): void {
            // Null means an official administrative decision is still required.
            $table->boolean('attendance_active')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('participant_applications', fn (Blueprint $table) => $table->dropColumn('attendance_active'));
    }
};
