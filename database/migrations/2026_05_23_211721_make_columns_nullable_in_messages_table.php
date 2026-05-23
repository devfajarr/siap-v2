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
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('matkul_id')->nullable()->change();
            $table->foreignId('jadwal_id')->nullable()->change();
            $table->foreignId('kelas_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('matkul_id')->nullable(false)->change();
            $table->foreignId('jadwal_id')->nullable(false)->change();
            $table->foreignId('kelas_id')->nullable(false)->change();
        });
    }
};
