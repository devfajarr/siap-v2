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
        Schema::table('questionnaires', function (Blueprint $table) {
            // Mengubah tipe kolom menjadi string agar dapat diisi target baru 'dosen_pegawai'
            $table->string('target_respondent')->default('all')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->enum('target_respondent', ['all', 'mahasiswa', 'dosen', 'pegawai'])->default('all')->change();
        });
    }
};
