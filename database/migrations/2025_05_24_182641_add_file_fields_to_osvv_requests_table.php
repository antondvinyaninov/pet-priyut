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
        Schema::table('osvv_requests', function (Blueprint $table) {
            $table->json('bite_medical_files')->nullable()->after('has_bite')
                ->comment('Файлы медицинских справок о укусе (JSON массив путей к файлам)');
            
            $table->json('bite_evidence_files')->nullable()->after('bite_medical_files')
                ->comment('Файлы фото/видео фиксации нападения (JSON массив путей к файлам)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('osvv_requests', function (Blueprint $table) {
            $table->dropColumn(['bite_medical_files', 'bite_evidence_files']);
        });
    }
};
