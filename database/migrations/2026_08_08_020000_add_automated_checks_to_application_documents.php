<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_application_documents', function (Blueprint $table): void {
            $table->string('automated_check_status')->nullable()->after('review_status');
            $table->json('automated_check_results')->nullable()->after('automated_check_status');
            $table->timestamp('automated_checked_at')->nullable()->after('automated_check_results');
        });
    }

    public function down(): void
    {
        Schema::table('participant_application_documents', function (Blueprint $table): void {
            $table->dropColumn(['automated_check_status', 'automated_check_results', 'automated_checked_at']);
        });
    }
};
