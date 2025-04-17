<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qcm_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->enum('difficulty', ['simple', 'moyen', 'difficile']);
            $table->unsignedBigInteger('correct_answer_id')->nullable(); // Sera défini ultérieurement
            $table->timestamps();

            // Index for correct_answer_id (optional, but can improve query performance)
            $table->index('correct_answer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
