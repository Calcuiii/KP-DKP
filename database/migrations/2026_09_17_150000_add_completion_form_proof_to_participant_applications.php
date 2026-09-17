<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_applications', function (Blueprint $table) {
            $table->string('completion_form_proof_path')->nullable();
            $table->timestamp('completion_form_submitted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('participant_applications', function (Blueprint $table) {
            $table->dropColumn(['completion_form_proof_path', 'completion_form_submitted_at']);
        });
    }
};
