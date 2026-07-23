<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['SOP', 'Panduan', 'FAQ', 'Template', 'Peraturan']);
            $table->string('type', 10);
            $table->string('version', 20)->default('1.0');
            $table->text('description')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('file_path');
            $table->unsignedInteger('chunks_count')->default(0);
            $table->enum('index_status', ['Pending', 'Processing', 'Ready', 'Failed'])->default('Pending');
            $table->enum('status', ['Ready', 'Pending', 'Processing', 'Failed'])->default('Pending');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_documents');
    }
};