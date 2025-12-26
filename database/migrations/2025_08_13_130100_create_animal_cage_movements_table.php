<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('animal_cage_movements')) {
            Schema::create('animal_cage_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('animal_id')->constrained()->onDelete('cascade');
                $table->string('from_cage')->nullable();
                $table->string('to_cage')->nullable();
                $table->text('comment')->nullable();
                $table->timestamp('moved_at')->nullable();
                $table->foreignId('moved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('animal_cage_movements')) {
            Schema::dropIfExists('animal_cage_movements');
        }
    }
};






