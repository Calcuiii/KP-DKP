<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unanswered_escalations', function (Blueprint $table): void {
            $table->text('admin_response')->nullable()->after('status');
            $table->foreignId('response_message_id')->nullable()->unique()->after('admin_response')->constrained('chat_messages')->nullOnDelete();
            $table->timestamp('responded_at')->nullable()->after('response_message_id');
        });
    }

    public function down(): void
    {
        Schema::table('unanswered_escalations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('response_message_id');
            $table->dropColumn(['admin_response', 'responded_at']);
        });
    }
};
