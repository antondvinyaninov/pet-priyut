<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animal_registration_cards', function (Blueprint $table) {
            $table->json('intake_location')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('animal_registration_cards', function (Blueprint $table) {
            $table->json('intake_location')->nullable(false)->change();
        });
    }
};
