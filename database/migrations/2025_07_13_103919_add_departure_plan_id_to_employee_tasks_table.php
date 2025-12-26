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
        Schema::table('employee_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_tasks', 'departure_plan_id')) {
                $table->foreignId('departure_plan_id')->nullable()->constrained('departure_plans')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('employee_tasks', 'departure_plan_id')) {
            Schema::table('employee_tasks', function (Blueprint $table) {
                $table->dropForeign(['departure_plan_id']);
                $table->dropColumn('departure_plan_id');
            });
        }
    }
};
