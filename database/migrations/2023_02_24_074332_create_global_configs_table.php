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
        Schema::create('global_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('chain');
            $table->string('key', '100');
            $table->string('value', '255');
            $table->timestamps();
            $table->unique(['chain', 'key'], 'unq_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_configs');
    }
};
