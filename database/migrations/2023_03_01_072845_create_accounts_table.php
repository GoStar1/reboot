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
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('account_id');
            $table->char('address', 42);
            $table->unsignedTinyInteger('chain')->default(2);
            $table->unsignedInteger('nonce')->default(0);
            $table->mediumText('private_key');
            $table->unsignedBigInteger('task_trans_id')->default(0);
            $table->unsignedDecimal('balance', 40, 18)->default(0);
            $table->unsignedTinyInteger('pending')->default(0);
            $table->text('tags');
            $table->timestamps();
            $table->fullText('tags', 'account_tags');
            $table->unique(['address', 'chain'], 'unq_chain_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
