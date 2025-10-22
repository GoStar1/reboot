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
        Schema::create('task_trans', function (Blueprint $table) {
            $table->id('task_trans_id');
            $table->unsignedTinyInteger('chain');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('task_data_id');
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('nonce');
            $table->char('from', 42);
            $table->char('to', 42);
            $table->unsignedDecimal('amount', 40, 18);
            $table->string('method', 50);
            $table->json('args');
            $table->mediumText('call_data')->nullable();
            $table->unsignedBigInteger('execute_time');
            $table->char('trans_hash', 66)->nullable();
            $table->unsignedBigInteger('send_trans_time')->default(0);
            $table->unsignedTinyInteger('status')->default(0);
            $table->unsignedTinyInteger('retry')->default(0);
            $table->mediumText('error')->nullable();
            $table->json('logs')->nullable();
            $table->timestamps();
            $table->unique('trans_hash', 'trans_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_trans');
    }
};
