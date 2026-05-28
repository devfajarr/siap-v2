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
        Schema::create('pengajuan_cetak_kartu_ujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->enum('jenis_ujian', ['uts', 'uas']);
            $table->string('bukti_spp');
            $table->string('bukti_pembayaran_ujian');
            $table->string('bulan_spp');
            $table->integer('tahun_spp');
            $table->tinyInteger('status')->default(0)->comment('0=Pending, 1=Selesai / Siap Diambil, 2=Ditolak');
            $table->text('keterangan')->nullable();
            $table->foreignId('petugas_id')->nullable()->constrained('admin')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cetak_kartu_ujians');
    }
};
