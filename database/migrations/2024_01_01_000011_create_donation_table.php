<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation', function (Blueprint $table) {
            $table->id('donation_id');
            $table->unsignedBigInteger('crisis_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('ldms_id')->nullable();
            $table->decimal('donation_amount', 12, 2);
            $table->timestamp('donation_date')->useCurrent();
            $table->string('payment_method', 50)->default('FPX');
            $table->string('donation_target', 100)->default('crisis_support');
            $table->string('donor_name', 150)->nullable();
            $table->string('donor_email')->nullable();
            $table->text('support_message')->nullable();
            $table->string('blockchain_hash', 128)->nullable();
            $table->timestamps();

            $table->foreign('crisis_id')->references('crisis_id')->on('crisis')->cascadeOnDelete();
            $table->foreign('user_id')->references('user_id')->on('public_users')->nullOnDelete();
            $table->foreign('ldms_id')->references('ldms_id')->on('ldms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation');
    }
};
