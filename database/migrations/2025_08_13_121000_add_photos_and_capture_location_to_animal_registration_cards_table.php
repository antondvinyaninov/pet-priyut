<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animal_registration_cards', function (Blueprint $table) {
            $table->string('photo_face')->nullable()->after('registration_number');
            $table->string('photo_profile')->nullable()->after('photo_face');
            $table->text('capture_location_address')->nullable()->after('vet_doc_date');
        });
    }

    public function down(): void
    {
        Schema::table('animal_registration_cards', function (Blueprint $table) {
            $table->dropColumn(['photo_face', 'photo_profile', 'capture_location_address']);
        });
    }
};






