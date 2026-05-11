<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_log', function (Blueprint $table) {
            $table->id('notification_id');
            $table->string('recipient_type', 30);
            $table->string('recipient_id', 50);
            $table->string('student_id', 20)->nullable();
            $table->string('notification_type', 80);
            $table->string('subject', 255)->nullable();
            $table->text('notification_message');
            $table->string('link', 500)->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id']);
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_log');
    }
};
