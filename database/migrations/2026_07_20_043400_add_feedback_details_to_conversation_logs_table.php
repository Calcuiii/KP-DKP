<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_logs', function (Blueprint $table) {
            $table->text('answer_preview')->nullable()->after('question');
            $table->string('feedback_reason', 100)->nullable()->after('feedback');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_logs', function (Blueprint $table) {
            $table->dropColumn(['answer_preview', 'feedback_reason']);
        });
    }
};