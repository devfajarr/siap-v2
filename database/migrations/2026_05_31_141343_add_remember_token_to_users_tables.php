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
        $tables = ['admin', 'direkturs', 'wadirs', 'kaprodi', 'mahasiswas', 'dosens', 'pegawais', 'jabatans', 'orang_tuas'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'remember_token')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->rememberToken();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['admin', 'direkturs', 'wadirs', 'kaprodi', 'mahasiswas', 'dosens', 'pegawais', 'jabatans', 'orang_tuas'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'remember_token')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('remember_token');
                });
            }
        }
    }
};
