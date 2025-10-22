<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->id('token_id');
            $table->string('name');
            $table->char('contract', 42);
            $table->unsignedTinyInteger('chain');
            $table->unsignedTinyInteger('decimals');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
