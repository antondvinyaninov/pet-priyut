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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email'); // Телефон
            $table->string('position')->nullable()->after('phone'); // Должность
            $table->text('bio')->nullable()->after('position'); // Биография/описание
            $table->boolean('is_active')->default(true)->after('bio'); // Активен ли пользователь
            $table->timestamp('last_login_at')->nullable()->after('is_active'); // Последний вход
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('last_login_at'); // Кто создал пользователя
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['phone', 'position', 'bio', 'is_active', 'last_login_at', 'created_by']);
        });
    }
};
