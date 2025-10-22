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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('chain');
            $table->unsignedInteger('block_number');
            $table->unsignedInteger('log_index');
            $table->char('trans_hash', 66);
            $table->char('contract_address', 42);
            $table->char('path', 42);
            $table->string('name', 250);
            $table->json('args');
            $table->unsignedInteger('retry')->default(0);
            $table->unsignedTinyInteger('processed')->default(0);
            $table->timestamp('created_at');
            $table->unique(['chain', 'block_number', 'log_index'], 'unq_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
