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
        Schema::create('feeder_wilayahs', function (Blueprint $table) {
            $table->string('id_wilayah', 10)->primary();
            $table->string('nama_wilayah')->index();
            $table->string('id_induk_wilayah', 10)->nullable()->index();
            $table->integer('id_level_wilayah')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeder_wilayahs');
    }
};
