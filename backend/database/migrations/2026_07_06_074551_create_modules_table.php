<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Addon / feature-module registry.
 * Each row is a toggleable capability of the platform (e.g. "live_classes",
 * "forums", "store"). When `enabled` is false, its routes, menus and APIs
 * are hidden. This is the backbone of the "enable only what you use" system.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // machine name, e.g. "live_classes"
            $table->string('name');                    // human name
            $table->string('description')->nullable();
            $table->string('icon')->nullable();        // lucide icon name for UI
            $table->string('category')->default('general');
            $table->boolean('enabled')->default(true);
            $table->boolean('is_core')->default(false); // core modules can't be disabled
            $table->json('settings')->nullable();       // per-module config
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
