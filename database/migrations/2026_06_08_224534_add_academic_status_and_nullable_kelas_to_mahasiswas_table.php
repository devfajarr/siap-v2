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
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
        });

        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->unsignedBigInteger('kelas_id')->nullable()->change();
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('set null');

            $table->string('status_mahasiswa')->default('Aktif')->index()->after('is_first_login');
            $table->string('id_periode_masuk')->nullable()->index()->after('status_mahasiswa');
            $table->string('id_jenis_keluar')->nullable()->after('id_periode_masuk');
            $table->date('tanggal_keluar')->nullable()->after('id_jenis_keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
        });

        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->unsignedBigInteger('kelas_id')->change();
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');

            $table->dropColumn(['status_mahasiswa', 'id_periode_masuk', 'id_jenis_keluar', 'tanggal_keluar']);
        });
    }
};
