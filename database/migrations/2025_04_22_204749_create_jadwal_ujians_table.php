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
        Schema::create('jadwal_ujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawais_id');
            $table->foreignId('matkuls_id');
            $table->foreignId('kelas_id');
            $table->foreignId('ruangans_id');
            $table->date('tanggal');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('tahun');
            $table->enum('jenis_ujian', ['uts', 'uas']);
            $table->softDeletes();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujians');
    }
};
