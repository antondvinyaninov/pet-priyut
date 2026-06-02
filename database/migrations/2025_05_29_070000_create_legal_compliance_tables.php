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
        // Таблица актов приема-передачи животных (Приказ №6)
        Schema::create('animal_transfer_acts', function (Blueprint $table) {
            $table->id();
            $table->string('act_number')->unique(); // Номер акта
            $table->date('act_date'); // Дата составления акта
            $table->enum('transfer_type', ['intake', 'release', 'return']); // Тип передачи
            $table->string('from_organization'); // Передающая организация
            $table->string('to_organization'); // Принимающая организация
            $table->json('from_representatives'); // Представители передающей стороны (ФИО, должности)
            $table->json('to_representatives'); // Представители принимающей стороны
            $table->text('transfer_reason')->nullable(); // Основание для передачи
            $table->text('notes')->nullable(); // Примечания
            $table->enum('status', ['draft', 'signed', 'completed'])->default('draft');
            $table->timestamps();
            
            $table->index(['act_date', 'transfer_type']);
        });

        // Связь актов с животными
        Schema::create('animal_transfer_act_animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_act_id')->constrained('animal_transfer_acts')->onDelete('cascade');
            $table->foreignId('animal_id')->constrained()->onDelete('cascade');
            $table->text('animal_condition')->nullable(); // Состояние животного при передаче
            $table->text('special_notes')->nullable(); // Особые отметки
            $table->timestamps();
            
            $table->unique(['transfer_act_id', 'animal_id']);
        });

        // Таблица комиссий по осмотру животных (Приказ №5)
        Schema::create('animal_inspection_commissions', function (Blueprint $table) {
            $table->id();
            $table->string('commission_name'); // Название комиссии
            $table->date('formation_date'); // Дата формирования
            $table->date('valid_until')->nullable(); // Действительна до
            $table->json('members'); // Состав комиссии (ФИО, должности)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Таблица актов осмотра животных
        Schema::create('animal_inspection_acts', function (Blueprint $table) {
            $table->id();
            $table->string('act_number')->unique(); // Номер акта осмотра
            $table->date('inspection_date'); // Дата осмотра
            $table->foreignId('commission_id')->constrained('animal_inspection_commissions');
            $table->foreignId('animal_id')->constrained()->onDelete('cascade');
            $table->enum('health_status', ['healthy', 'sick', 'injured', 'critical']); // Состояние здоровья
            $table->enum('aggression_level', [
                'none', // Отсутствует
                'low', // Низкая
                'moderate', // Умеренная 
                'high', // Высокая
                'unmotivated' // Немотивированная агрессивность
            ]); // Уровень агрессивности
            $table->boolean('sterilization_required')->default(false); // Требуется стерилизация
            $table->boolean('is_sterilized')->default(false); // Стерилизовано
            $table->date('sterilization_date')->nullable(); // Дата стерилизации
            $table->string('sterilization_mark')->nullable(); // Отметка о стерилизации (клипса, чип и т.д.)
            $table->boolean('return_to_habitat_allowed')->default(false); // Разрешен возврат на место обитания
            $table->text('return_conditions')->nullable(); // Условия возврата
            $table->text('inspection_notes')->nullable(); // Заключение комиссии
            $table->json('commission_signatures'); // Подписи членов комиссии
            $table->enum('status', ['draft', 'signed', 'completed'])->default('draft');
            $table->timestamps();
            
            $table->index(['inspection_date', 'aggression_level']);
            $table->index(['sterilization_required', 'is_sterilized']);
        });

        // Таблица процедур возврата животных на места обитания
        Schema::create('animal_return_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained()->onDelete('cascade');
            $table->foreignId('inspection_act_id')->constrained('animal_inspection_acts');
            $table->string('original_location'); // Место первоначального обитания
            $table->json('location_coordinates')->nullable(); // Координаты места
            $table->date('planned_return_date'); // Планируемая дата возврата
            $table->date('actual_return_date')->nullable(); // Фактическая дата возврата
            $table->enum('return_status', [
                'planned', // Запланирован
                'approved', // Одобрен
                'in_progress', // В процессе
                'completed', // Выполнен
                'cancelled' // Отменен
            ])->default('planned');
            $table->text('return_conditions_met')->nullable(); // Выполненные условия
            $table->text('return_notes')->nullable(); // Примечания к возврату
            $table->json('responsible_persons'); // Ответственные лица
            $table->timestamps();
            
            $table->index(['planned_return_date', 'return_status']);
        });

        // Таблица карточек учета животных (расширение существующей информации)
        Schema::create('animal_registration_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained()->onDelete('cascade');
            $table->string('registration_number')->unique(); // Учетный номер
            $table->date('registration_date'); // Дата постановки на учет
            $table->string('intake_source'); // Источник поступления (ОСВВ, находка и т.д.)
            $table->json('intake_location'); // Место обнаружения/поступления
            $table->text('physical_description'); // Физическое описание
            $table->json('special_marks')->nullable(); // Особые приметы (клеймо, чип и т.д.)
            $table->enum('reproductive_status', [
                'intact', // Не стерилизован
                'sterilized', // Стерилизован
                'castrated', // Кастрирован
                'unknown' // Неизвестно
            ])->default('unknown');
            $table->text('veterinary_notes')->nullable(); // Ветеринарные заметки
            $table->json('vaccination_history')->nullable(); // История вакцинации
            $table->enum('card_status', ['active', 'archived', 'transferred'])->default('active');
            $table->timestamps();
            
            $table->unique('animal_id');
            $table->index(['registration_date', 'intake_source']);
        });

        // Таблица нормативных документов для отслеживания соответствия
        Schema::create('regulatory_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_type'); // Тип документа (приказ, постановление и т.д.)
            $table->string('document_number'); // Номер документа
            $table->date('document_date'); // Дата документа
            $table->string('issuing_authority'); // Издавший орган
            $table->string('title'); // Название/предмет
            $table->text('description')->nullable(); // Описание
            $table->string('file_path')->nullable(); // Путь к файлу документа
            $table->boolean('is_active')->default(true); // Действующий
            $table->date('effective_from'); // Действует с
            $table->date('effective_until')->nullable(); // Действует до
            $table->timestamps();
            
            $table->index(['document_date', 'is_active']);
            $table->unique(['document_number', 'document_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulatory_documents');
        Schema::dropIfExists('animal_registration_cards');
        Schema::dropIfExists('animal_return_procedures');
        Schema::dropIfExists('animal_inspection_acts');
        Schema::dropIfExists('animal_inspection_commissions');
        Schema::dropIfExists('animal_transfer_act_animals');
        Schema::dropIfExists('animal_transfer_acts');
    }
}; 