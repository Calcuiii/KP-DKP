<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_key')->index();
            $table->string('title', 90);
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_conversation_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant']);
            $table->text('content');
            $table->string('status', 50);
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_message_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_message_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('document_id');
            $table->string('document_title');
            $table->string('section_title');
            $table->unsignedSmallInteger('position');
            $table->timestamps();

            $table->unique(['chat_message_id', 'position']);
        });

        Schema::create('chat_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_message_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('rating', ['positive', 'negative']);
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_feedback');
        Schema::dropIfExists('chat_message_sources');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
