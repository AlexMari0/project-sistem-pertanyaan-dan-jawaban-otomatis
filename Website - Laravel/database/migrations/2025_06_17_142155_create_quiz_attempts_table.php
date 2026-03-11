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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');

            // Skor akhir sebagai persentase (misal: 75.00%)
            $table->decimal('score', 5, 2)->default(0)->comment('Final percentage score (1-100)');

            // Tambahan kolom baru untuk penilaian berbasis cosine similarity
            $table->integer('raw_score')->nullable()->comment('Total points earned (sum of individual question scores)');
            $table->integer('max_score')->nullable()->comment('Maximum possible points');
            $table->string('scoring_method', 50)->default('exact_match')->comment('Method used for scoring (exact_match, cosine_similarity)');

            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->json('answers')->nullable();
            $table->timestamps();

            $table->unique(['quiz_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
