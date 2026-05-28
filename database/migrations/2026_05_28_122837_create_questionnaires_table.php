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
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['pelayanan', 'kinerja_pengajar', 'ami']);
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->enum('target_respondent', ['all', 'mahasiswa', 'dosen', 'pegawai'])->default('all');
            $table->nullableMorphs('created_by'); // created_by_id & created_by_type (Admin atau BPMI)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};
