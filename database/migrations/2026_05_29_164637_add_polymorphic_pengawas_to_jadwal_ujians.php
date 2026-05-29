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
        Schema::table('jadwal_ujians', function (Blueprint $table) {
            $table->renameColumn('pegawais_id', 'pengawas_id');
        });
        Schema::table('jadwal_ujians', function (Blueprint $table) {
            $table->string('pengawas_type')->after('pengawas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_ujians', function (Blueprint $table) {
            $table->dropColumn('pengawas_type');
        });
        Schema::table('jadwal_ujians', function (Blueprint $table) {
            $table->renameColumn('pengawas_id', 'pegawais_id');
        });
    }
};
