<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_applications', function (Blueprint $table) {
            $table->json('completion_checklist')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('participant_applications', function (Blueprint $table) {
            $table->dropColumn('completion_checklist');
        });
    }
};
