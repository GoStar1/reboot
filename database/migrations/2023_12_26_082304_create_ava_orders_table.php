<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ava_orders', function (Blueprint $table) {
            $table->id();
            $table->char('seller', 42);
            $table->char('creator', 42);
            $table->char('list_id', 66);
            $table->string('ticker', 40);
            $table->string('amount', 100);
            $table->string('price', 100);
            $table->string('nonce', 100);
            $table->unsignedBigInteger('listing_time');
            $table->unsignedBigInteger('expiration_time');
            $table->string('creator_fee_rate', 100);
            $table->string('salt', 100);
            $table->string('extra_params', 20);
            $table->string('r');
            $table->string('s');
            $table->string('v', 20);
            $table->unsignedDecimal('total_price', 40, 18);
            $table->char('confirm_trans_hash', 66)->nullable();
            $table->char('taker', 42)->nullable();
            $table->unsignedTinyInteger('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ava_orders');
    }
};
