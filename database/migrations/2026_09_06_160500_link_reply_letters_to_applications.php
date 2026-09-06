<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reply_letters', function (Blueprint $table) {
            $table->foreignId('participant_application_id')
                ->nullable()
                ->after('participant_id')
                ->constrained('participant_applications')
                ->nullOnDelete();
        });

        DB::table('participant_applications')
            ->whereNotNull('response_letter_path')
            ->orderBy('id')
            ->each(function ($application): void {
                DB::table('reply_letters')
                    ->where('participant_id', $application->participant_id)
                    ->where('file_path', $application->response_letter_path)
                    ->update(['participant_application_id' => $application->id]);
            });
    }

    public function down(): void
    {
        Schema::table('reply_letters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('participant_application_id');
        });
    }
};
