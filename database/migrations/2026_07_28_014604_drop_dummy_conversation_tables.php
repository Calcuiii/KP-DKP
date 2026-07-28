<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('conversation_logs');
        Schema::dropIfExists('unanswered_questions');
    }

    public function down(): void
    {
        // Sengaja tidak di-restore — tabel dummy sudah digantikan chat_conversations/chat_messages.
    }
};