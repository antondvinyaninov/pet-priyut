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
        Schema::table('animal_registration_cards', function (Blueprint $table) {
            $table->string('category')->nullable()->after('registration_date'); // собака/щенок/кошка/котенок
            $table->string('coat')->nullable()->after('physical_description'); // шерсть
            $table->string('ears')->nullable()->after('coat'); // уши
            $table->string('tail')->nullable()->after('ears'); // хвост
            $table->string('size')->nullable()->after('tail'); // размер

            $table->date('clinical_exam_date')->nullable()->after('intake_location');
            $table->text('clinical_exam_conclusion')->nullable()->after('clinical_exam_date');

            $table->text('aggression_notes')->nullable()->after('clinical_exam_conclusion');
            $table->text('behavior_correction_notes')->nullable()->after('aggression_notes');

            $table->date('deworming_date')->nullable()->after('behavior_correction_notes');
            $table->date('sterilization_date')->nullable()->after('deworming_date');
            $table->string('sterilization_vet')->nullable()->after('sterilization_date');

            $table->date('marking_date')->nullable()->after('sterilization_vet');
            $table->string('tag_number_card')->nullable()->after('marking_date');
            $table->string('chip_number_card')->nullable()->after('tag_number_card');

            $table->string('vet_doc_number')->nullable()->after('chip_number_card'); // ВСД номер
            $table->date('vet_doc_date')->nullable()->after('vet_doc_number'); // ВСД дата

            $table->string('capture_act_number')->nullable()->after('vet_doc_date');
            $table->date('capture_act_date')->nullable()->after('capture_act_number');

            $table->string('aggression_act_number')->nullable()->after('capture_act_date');
            $table->date('aggression_act_date')->nullable()->after('aggression_act_number');

            $table->text('outcome_reason')->nullable()->after('card_status');
            $table->date('outcome_date')->nullable()->after('outcome_reason');

            $table->text('release_address')->nullable()->after('outcome_date');

            $table->string('new_owner_type')->nullable()->after('release_address'); // legal|individual
            $table->string('new_owner_name')->nullable()->after('new_owner_type');
            $table->text('new_owner_address')->nullable()->after('new_owner_name');
            $table->string('new_owner_phone')->nullable()->after('new_owner_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('animal_registration_cards', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'coat', 'ears', 'tail', 'size',
                'clinical_exam_date', 'clinical_exam_conclusion',
                'aggression_notes', 'behavior_correction_notes',
                'deworming_date', 'sterilization_date', 'sterilization_vet',
                'marking_date', 'tag_number_card', 'chip_number_card',
                'vet_doc_number', 'vet_doc_date',
                'capture_act_number', 'capture_act_date',
                'aggression_act_number', 'aggression_act_date',
                'outcome_reason', 'outcome_date',
                'release_address',
                'new_owner_type', 'new_owner_name', 'new_owner_address', 'new_owner_phone',
            ]);
        });
    }
};






