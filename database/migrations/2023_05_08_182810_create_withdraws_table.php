<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('withdraws', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type');
            $table->string('url', 255);
            $table->string('method', 50);
            $table->json('headers');
            $table->mediumText('extra');
            $table->json('req');
            $table->json('response');
            $table->unsignedTinyInteger('status');
            $table->unsignedBigInteger('req_time');
            $table->unsignedBigInteger('response_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdraws');
    }
};
