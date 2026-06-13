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
        Schema::table('dosens', function (Blueprint $table) {
            $table->uuid('feeder_id_dosen')->nullable()->index()->after('nidn');
            $table->uuid('feeder_id_registrasi')->nullable()->index()->after('feeder_id_dosen');
            $table->foreignId('feeder_dosen_placeholder_id')
                ->nullable()
                ->after('feeder_id_registrasi')
                ->constrained('dosens')
                ->onDelete('set null');
        });

        Schema::table('matkuls', function (Blueprint $table) {
            $table->uuid('feeder_id_matkul')->nullable()->index()->after('id');
        });

        Schema::table('jadwals', function (Blueprint $table) {
            $table->uuid('feeder_id_kelas')->nullable()->index()->after('id');
            $table->foreignId('feeder_override_dosen_id')
                ->nullable()
                ->after('dosens_id')
                ->constrained('dosens')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropForeign(['feeder_override_dosen_id']);
            $table->dropColumn(['feeder_id_kelas', 'feeder_override_dosen_id']);
        });

        Schema::table('matkuls', function (Blueprint $table) {
            $table->dropColumn(['feeder_id_matkul']);
        });

        Schema::table('dosens', function (Blueprint $table) {
            $table->dropForeign(['feeder_dosen_placeholder_id']);
            $table->dropColumn(['feeder_id_dosen', 'feeder_id_registrasi', 'feeder_dosen_placeholder_id']);
        });
    }
};
