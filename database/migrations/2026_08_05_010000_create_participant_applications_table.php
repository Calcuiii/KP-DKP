<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->string('service_type');
            $table->string('status')->default('preparation');
            $table->timestamp('google_form_confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['participant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_applications');
    }
};
