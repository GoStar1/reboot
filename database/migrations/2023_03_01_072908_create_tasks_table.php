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
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('task_id');
            $table->unsignedBigInteger('task_data_id');
            $table->unsignedBigInteger('template_id');
            $table->string('method', 50);
            $table->unsignedDecimal('amount', 40, 18);
            $table->json('args');
            $table->mediumText('call_data')->nullable();
            $table->json('save_data');
            $table->unsignedBigInteger('start_time');
            $table->unsignedBigInteger('time_range');
            $table->unsignedInteger('completed')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->unsignedDecimal('min_gas_price')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
