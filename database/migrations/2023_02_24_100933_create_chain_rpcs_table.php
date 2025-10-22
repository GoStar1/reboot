<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chain_rpcs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('chain');
            $table->string('url', 255);
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedTinyInteger('heartbeat')->default(1);
            $table->unsignedBigInteger('block_number');
            $table->unsignedDecimal('resp_time', 8, 3);
            $table->unsignedTinyInteger('priority')->default(255);
            $table->timestamps();
            $table->unique(['chain', 'url'], 'blockchain_chain_rpc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chain_rpcs');
    }
};
