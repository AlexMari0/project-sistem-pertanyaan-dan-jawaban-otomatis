<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->constrained()->onDelete('cascade');

            $table->unsignedBigInteger('teacher_id');

            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->unsignedInteger('time_limit')->nullable()->comment('In minutes');
            $table->unsignedInteger('total_points')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('teacher_id')->references('user_id')->on('users')->onDelete('cascade');

            $table->index(['course_id', 'teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
