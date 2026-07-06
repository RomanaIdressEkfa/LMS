<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenants — customer academies the platform owner resells Nova LMS to. Each
 * tenant is on a plan, can have per-tenant module overrides, its own price,
 * branding and status.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();          // subdomain-style handle
            $table->string('owner_name')->nullable();
            $table->string('owner_email')->nullable();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->decimal('price_override', 10, 2)->nullable(); // custom price for this tenant
            $table->json('module_overrides')->nullable();         // explicit enabled module keys (else plan's)
            $table->string('primary_color')->default('#2563ff');
            $table->string('status')->default('trial'); // trial|active|suspended
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
