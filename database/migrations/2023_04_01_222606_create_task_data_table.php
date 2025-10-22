<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_data', function (Blueprint $table) {
            $table->id('task_data_id');
            $table->unsignedTinyInteger('chain');
            $table->string('name', 120);
            $table->unsignedInteger('accounts');
            $table->unsignedInteger('tasks');
            $table->timestamps();
            $table->unique('name', 'task_unq_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_data');
    }
};
