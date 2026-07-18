<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payment gateway registry.
 * Admins toggle each gateway on/off and store its credentials. Only enabled
 * gateways appear at checkout. Credentials are encrypted at the model layer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();      // e.g. "stripe", "paypal", "sslcommerz"
            $table->string('name');
            $table->string('logo')->nullable();
            $table->boolean('enabled')->default(false);
            $table->boolean('test_mode')->default(true);
            $table->json('credentials')->nullable(); // encrypted via cast
            $table->string('currency')->default('USD');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
