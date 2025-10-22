<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_data_accounts', function (Blueprint $table) {
            $table->id('task_account_id');
            $table->unsignedBigInteger('task_data_id');
            $table->char('address', 42);
            $table->unsignedBigInteger('account_id');
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_data_accounts');
    }
};
