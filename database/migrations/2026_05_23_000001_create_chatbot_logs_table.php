<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_role', 20)->default('public');
            $table->string('language', 5)->default('en');
            // We hash the message instead of storing raw text — PDPA-friendly,
            // matches your blockchain audit pattern (SHA-256 of payload).
            $table->string('message_hash', 64);
            $table->boolean('escalated')->default(false);
            $table->string('escalation_reason', 50)->nullable();
            $table->timestamps();

            $table->index(['user_role', 'escalated']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_logs');
    }
};
