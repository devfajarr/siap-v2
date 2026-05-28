<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->constrained('questionnaire_sections')->nullOnDelete();
        });

        // Backfill: Buat seksi default untuk kuisioner lama yang sudah ada di DB
        $questionnaires = DB::table('questionnaires')->get();
        foreach ($questionnaires as $q) {
            $sectionId = DB::table('questionnaire_sections')->insertGetId([
                'questionnaire_id' => $q->id,
                'title' => 'Bagian Utama',
                'description' => 'Silakan isi pertanyaan di bawah ini.',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('questions')
                ->where('questionnaire_id', $q->id)
                ->update(['section_id' => $sectionId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
    }
};
