<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_application_documents', function (Blueprint $table): void {
            $table->boolean('certificate_eligible')
                ->nullable()
                ->default(true)
                ->after('review_notes');
        });
    }

    public function down(): void
    {
        Schema::table('participant_application_documents', function (Blueprint $table): void {
            $table->dropColumn('certificate_eligible');
        });
    }
};