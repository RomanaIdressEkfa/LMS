<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gateway credentials are encrypted (encrypted:array cast) into an opaque
 * string, which a JSON column rejects. Store them as TEXT instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->text('credentials')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->json('credentials')->nullable()->change();
        });
    }
};
