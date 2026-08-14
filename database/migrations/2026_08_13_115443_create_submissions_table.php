<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference_number', 30)->unique(); // KP-2025-0042
            $table->enum('type', ['magang', 'wopps']);
            $table->string('title');
            $table->unsignedTinyInteger('step')->default(1); // 1-8
            $table->string('step_label');
            $table->enum('action_state', ['waiting', 'needs-revision'])->default('waiting');
            $table->text('revision_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};