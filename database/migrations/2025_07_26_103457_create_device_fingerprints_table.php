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
        Schema::create('device_fingerprints', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint_hash')->unique();
            $table->json('browser_info'); // Browser, version, etc.
            $table->json('device_info'); // Screen resolution, timezone, etc.
            $table->string('ip_address');
            $table->string('user_agent');
            $table->enum('status', ['trusted', 'suspicious', 'blocked'])->default('trusted');
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->integer('usage_count')->default(1);
            $table->timestamps();

            $table->index(['fingerprint_hash', 'status']);
            $table->index(['ip_address', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_fingerprints');
    }
};
