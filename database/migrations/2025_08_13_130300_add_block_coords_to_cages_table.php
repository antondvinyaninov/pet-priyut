<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cages', function (Blueprint $table) {
            if (!Schema::hasColumn('cages', 'cage_block_id')) {
                $table->foreignId('cage_block_id')->nullable()->constrained('cage_blocks')->nullOnDelete();
            }
            if (!Schema::hasColumn('cages', 'row_index')) {
                $table->unsignedInteger('row_index')->nullable();
            }
            if (!Schema::hasColumn('cages', 'col_index')) {
                $table->unsignedInteger('col_index')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cages', function (Blueprint $table) {
            if (Schema::hasColumn('cages', 'cage_block_id')) {
                $table->dropConstrainedForeignId('cage_block_id');
            }
            if (Schema::hasColumn('cages', 'row_index')) {
                $table->dropColumn('row_index');
            }
            if (Schema::hasColumn('cages', 'col_index')) {
                $table->dropColumn('col_index');
            }
        });
    }
};






