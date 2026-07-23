<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unanswered_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->string('category', 50);
            $table->unsignedInteger('frequency')->default(1);
            $table->decimal('score', 4, 2)->default(0);
            $table->date('first_asked');
            $table->date('last_asked');
            $table->enum('status', ['Baru', 'Ditinjau', 'Perlu Update KB', 'Selesai'])->default('Baru');
            $table->text('fallback_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unanswered_questions');
    }
};