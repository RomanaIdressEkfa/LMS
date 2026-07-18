<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds an uploaded-video path and a per-lesson quiz question. Answering the
 * question correctly completes the lesson and unlocks the next one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('video_file')->nullable()->after('video_url'); // uploaded file path
            $table->text('question')->nullable()->after('content');
            $table->json('question_options')->nullable()->after('question');
            $table->unsignedTinyInteger('question_correct_index')->nullable()->after('question_options');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['video_file', 'question', 'question_options', 'question_correct_index']);
        });
    }
};
