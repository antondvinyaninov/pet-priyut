<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('osvv_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('osvv_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained(); // Кто оставил комментарий
            $table->text('comment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('osvv_comments');
    }
};
