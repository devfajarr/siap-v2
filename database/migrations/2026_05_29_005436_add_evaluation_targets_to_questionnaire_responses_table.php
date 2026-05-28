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
        Schema::table('questionnaire_responses', function (Blueprint $table) {
            $table->foreignId('dosen_id')->nullable()->after('respondent_type')->constrained('dosens')->nullOnDelete();
            $table->foreignId('matkul_id')->nullable()->after('dosen_id')->constrained('matkuls')->nullOnDelete();
            $table->foreignId('jadwal_id')->nullable()->after('matkul_id')->constrained('jadwals')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaire_responses', function (Blueprint $table) {
            $table->dropForeign(['dosen_id']);
            $table->dropForeign(['matkul_id']);
            $table->dropForeign(['jadwal_id']);
            $table->dropColumn(['dosen_id', 'matkul_id', 'jadwal_id']);
        });
    }
};
