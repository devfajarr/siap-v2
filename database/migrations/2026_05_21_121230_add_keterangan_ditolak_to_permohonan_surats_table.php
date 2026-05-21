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
        Schema::table('permohonan_surats', function (Blueprint $table) {
            $table->text('keterangan_ditolak')->nullable()->after('pimpinan');
            // setuju_kaprodi: 0 = Pending, 1 = Disetujui, 2 = Ditolak
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan_surats', function (Blueprint $table) {
            $table->dropColumn('keterangan_ditolak');
        });
    }
};
