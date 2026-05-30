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
        $tables = ['mahasiswas', 'dosens', 'pegawais', 'admins', 'direkturs', 'wadirs', 'kaprodis', 'jabatans'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                if (! Schema::hasColumn($tableName, 'whatsapp_verified_at')) {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        if (Schema::hasColumn($tableName, 'no_telephone')) {
                            $table->timestamp('whatsapp_verified_at')->nullable()->after('no_telephone');
                        } else {
                            $table->timestamp('whatsapp_verified_at')->nullable();
                        }
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['mahasiswas', 'dosens', 'pegawais', 'admins', 'direkturs', 'wadirs', 'kaprodis', 'jabatans'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                if (Schema::hasColumn($tableName, 'whatsapp_verified_at')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->dropColumn('whatsapp_verified_at');
                    });
                }
            }
        }
    }
};
