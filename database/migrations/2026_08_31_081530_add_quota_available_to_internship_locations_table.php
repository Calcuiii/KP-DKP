<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internship_locations', function (Blueprint $table): void {
            $table->unsignedInteger('quota_available')
                ->nullable()
                ->after('quota_status');
        });
    }

    public function down(): void
    {
        Schema::table('internship_locations', function (Blueprint $table): void {
            $table->dropColumn('quota_available');
        });
    }
};