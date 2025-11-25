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
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->string('command'); // 'enroll_fingerprint', 'delete_fingerprint', etc.
            $table->json('payload')->nullable(); // Parameters for the command
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('result')->nullable(); // Output from the device
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_commands');
    }
};
