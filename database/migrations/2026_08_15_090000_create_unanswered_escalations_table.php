<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unanswered_escalations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assistant_message_id')->unique()->constrained('chat_messages')->cascadeOnDelete();
            $table->foreignId('user_message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->string('ticket_code', 24)->unique();
            $table->string('status', 24)->default('new')->index();
            $table->string('whatsapp_status', 24)->default('pending')->index();
            $table->text('whatsapp_error')->nullable();
            $table->timestamp('whatsapp_sent_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unanswered_escalations');
    }
};
