<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedTinyInteger('super_admin_slot')->nullable()->unique();
        });

        $superAdminIds = DB::table('users')
            ->where('role', 'superadmin')
            ->orderBy('id')
            ->pluck('id');

        if ($superAdminIds->isEmpty()) {
            return;
        }

        DB::table('users')
            ->where('id', $superAdminIds->first())
            ->update(['super_admin_slot' => 1]);

        if ($superAdminIds->count() > 1) {
            DB::table('users')
                ->whereIn('id', $superAdminIds->slice(1)->all())
                ->update([
                    'role' => 'admin',
                    'super_admin_slot' => null,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['super_admin_slot']);
            $table->dropColumn('super_admin_slot');
        });
    }
};
