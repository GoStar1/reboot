<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('path', 255);
            $table->mediumText('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_operations');
    }
};
