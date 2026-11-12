<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_ai_counters', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('scope', 24)->index();
            $table->string('period', 8);
            $table->timestamp('expires_at')->index();
            foreach (['requests', 'tokens', 'actual_tokens', 'successes', 'failures', 'cache_hits', 'fallbacks'] as $column) {
                $table->unsignedBigInteger($column)->default(0);
            }
            $table->string('last_error', 64)->nullable();
            $table->json('provider_limits')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_ai_counters');
    }
};
