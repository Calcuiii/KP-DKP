<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // C-001, C-002, dst
            $table->text('question');
            $table->string('category', 50);
            $table->enum('status', ['Dijawab', 'Tidak Ditemukan', 'Error'])->default('Dijawab');
            $table->unsignedTinyInteger('sources')->default(0);
            $table->decimal('score', 4, 2)->default(0);
            $table->decimal('response_time', 5, 2)->default(0); // detik
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_logs');
    }
};