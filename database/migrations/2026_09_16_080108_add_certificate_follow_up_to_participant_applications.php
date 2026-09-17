<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_applications', function (Blueprint $table): void {
            $table->string('certificate_follow_up_choice')
                ->nullable()
                ->after('response_letter_path');

            $table->timestamp('certificate_follow_up_at')
                ->nullable()
                ->after('certificate_follow_up_choice');
        });
    }

    public function down(): void
    {
        Schema::table('participant_applications', function (Blueprint $table): void {
            $table->dropColumn([
                'certificate_follow_up_choice',
                'certificate_follow_up_at'
            ]);
        });
    }
};