<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_application_documents', function (Blueprint $table) {
            $table->string('letter_institution')->nullable();
            $table->string('letter_number')->nullable();
            $table->string('letter_group_key', 64)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('participant_application_documents', function (Blueprint $table) {
            $table->dropIndex(['letter_group_key']);
            $table->dropColumn(['letter_institution', 'letter_number', 'letter_group_key']);
        });
    }
};
