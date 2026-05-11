<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blockchain', function (Blueprint $table) {
            $table->id('blockchain_id');
            $table->string('data_from', 80);
            $table->string('data_type', 80);
            $table->string('stored_data', 128);
            $table->string('hash_type', 30)->default('SHA-256');
            $table->boolean('verified')->default(true);
            $table->timestamp('timestamp')->useCurrent();
            $table->string('tx_hash', 128)->nullable();
            $table->enum('mode', ['quorum', 'simulation'])->default('simulation');
            $table->string('reference_table', 80)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('payload_meta')->nullable();
            $table->timestamps();

            $table->index('data_from');
            $table->index('stored_data');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blockchain');
    }
};
