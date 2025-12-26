<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('cages')) {
            Schema::create('cages', function (Blueprint $table) {
                $table->id();
                $table->string('number')->unique();
                $table->string('title')->nullable();
                $table->unsignedInteger('capacity')->default(2);
                $table->json('layout')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cages')) {
            Schema::dropIfExists('cages');
        }
    }
};






