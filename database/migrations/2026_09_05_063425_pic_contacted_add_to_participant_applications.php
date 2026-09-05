<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_applications', function (Blueprint $table): void {
            $table->timestamp('pic_contacted_at')
                ->nullable()
                ->after('google_form_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('participant_applications', function (Blueprint $table): void {
            $table->dropColumn('pic_contacted_at');
        });
    }
};