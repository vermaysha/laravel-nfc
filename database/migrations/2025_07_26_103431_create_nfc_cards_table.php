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
        Schema::create('nfc_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('card_uid')->unique(); // NFC card unique identifier
            $table->text('public_key'); // RSA public key for verification
            $table->text('encrypted_data'); // Encrypted employee data
            $table->string('signature'); // Digital signature for integrity
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->timestamps();

            $table->index(['card_uid', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfc_cards');
    }
};
