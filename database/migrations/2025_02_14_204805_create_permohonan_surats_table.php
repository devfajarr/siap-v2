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
        Schema::create('permohonan_surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained()->references('id')->on('mahasiswas');
            $table->boolean('setuju_kaprodi');
            $table->boolean('status');
            $table->string('jenis_permohonan');
            $table->string('nama_orang_tua')->nullable();
            $table->text('alamat_orang_tua')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('nip')->nullable();
            $table->string('pangkat_golongan')->nullable();
            $table->string('nama_instansi')->nullable();
            $table->string('alamat_instansi')->nullable();
            $table->string('keperluan')->nullable();
            $table->string('tahun_akademik')->nullable();
            $table->text('masa_cuti')->nullable();
            $table->text('alasan_cuti')->nullable();
            $table->string('kelas_asal')->nullable();
            $table->string('kelas_tujuan')->nullable();
            $table->text('pt_asal')->nullable();
            $table->text('pt_tujuan')->nullable();
            $table->string('status_akreditasi')->nullable();
            $table->text('judul_laporan')->nullable();
            $table->json('data_diminta')->nullable();
            $table->string('no_surat')->nullable();
            $table->text('pimpinan')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_surats');
    }
};
