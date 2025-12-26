<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cage_blocks')) {
            Schema::create('cage_blocks', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedInteger('rows')->default(1);
                $table->unsignedInteger('cols')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cage_blocks');
    }
};






