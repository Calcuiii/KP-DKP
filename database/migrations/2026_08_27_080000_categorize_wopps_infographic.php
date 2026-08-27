<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('infographics')
            ->where('series_number', 7)
            ->where('type', 'infografis')
            ->update(['type' => 'infografis_wopps']);
    }

    public function down(): void
    {
        DB::table('infographics')
            ->where('series_number', 7)
            ->where('type', 'infografis_wopps')
            ->update(['type' => 'infografis']);
    }
};
