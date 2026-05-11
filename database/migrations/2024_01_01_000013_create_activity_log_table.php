<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id('log_id');
            $table->string('user_type', 30);
            $table->string('user_id', 50);
            $table->string('action', 100);
            $table->boolean('active')->default(true);
            $table->timestamp('timestamp')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->text('action_description')->nullable();
            $table->timestamps();

            $table->index(['user_type', 'user_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
