<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animal_registration_cards', function (Blueprint $table) {
            // Делаем все текстовые поля nullable
            $table->text('physical_description')->nullable()->change();
            $table->json('special_marks')->nullable()->change();
            $table->string('reproductive_status')->nullable()->change();
            $table->text('veterinary_notes')->nullable()->change();
            $table->json('vaccination_history')->nullable()->change();
            $table->string('card_status')->nullable()->change();
            $table->string('category')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('animal_registration_cards', function (Blueprint $table) {
            $table->text('physical_description')->nullable(false)->change();
            $table->json('special_marks')->nullable(false)->change();
            $table->string('reproductive_status')->nullable(false)->change();
            $table->text('veterinary_notes')->nullable(false)->change();
            $table->json('vaccination_history')->nullable(false)->change();
            $table->string('card_status')->nullable(false)->change();
            $table->string('category')->nullable(false)->change();
        });
    }
};
