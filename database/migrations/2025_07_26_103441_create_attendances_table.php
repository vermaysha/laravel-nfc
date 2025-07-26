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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('nfc_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_fingerprint_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['check_in', 'check_out']);
            $table->timestamp('scanned_at');
            $table->string('location')->nullable();
            $table->json('scan_metadata')->nullable(); // Additional scan data
            $table->enum('status', ['valid', 'invalid', 'suspicious'])->default('valid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'scanned_at']);
            $table->index(['nfc_card_id', 'scanned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
