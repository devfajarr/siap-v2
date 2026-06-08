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
            $table->uuid('feeder_id_mahasiswa')->nullable()->index()->after('dosen_pembimbing_id');
            $table->uuid('feeder_id_registrasi')->nullable()->index()->after('feeder_id_mahasiswa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn(['feeder_id_mahasiswa', 'feeder_id_registrasi']);
        });
    }
};
