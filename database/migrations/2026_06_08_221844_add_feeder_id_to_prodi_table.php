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
        Schema::table('prodi', function (Blueprint $table) {
            $table->string('feeder_id_prodi')->nullable()->unique()->index()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prodi', function (Blueprint $table) {
            $table->dropColumn('feeder_id_prodi');
        });
    }
};
