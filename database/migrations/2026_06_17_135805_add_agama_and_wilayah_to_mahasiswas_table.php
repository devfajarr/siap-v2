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
            $table->unsignedInteger('id_agama')->nullable()->after('jenis_kelamin');
            $table->string('id_wilayah', 10)->nullable()->after('id_agama')->index();

            $table->foreign('id_agama')->references('id_agama')->on('feeder_agamas')->onDelete('set null');
            $table->foreign('id_wilayah')->references('id_wilayah')->on('feeder_wilayahs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['id_agama']);
            $table->dropForeign(['id_wilayah']);
            $table->dropColumn(['id_agama', 'id_wilayah']);
        });
    }
};
