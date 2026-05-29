<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create pivot table
        Schema::create('kaprodi_prodi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kaprodi_id')->constrained('kaprodi')->onDelete('cascade');
            $table->foreignId('prodi_id')->constrained('prodi')->onDelete('cascade');
            $table->timestamps();
        });

        // 2. Migrate existing Kaprodi relations to pivot table
        $existing = DB::table('kaprodi')->whereNotNull('prodis_id')->get();
        foreach ($existing as $kaprodi) {
            DB::table('kaprodi_prodi')->insert([
                'kaprodi_id' => $kaprodi->id,
                'prodi_id' => $kaprodi->prodis_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Drop column prodis_id from kaprodi table
        Schema::table('kaprodi', function (Blueprint $table) {
            $table->dropForeign(['prodis_id']);
            $table->dropColumn('prodis_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add prodis_id back to kaprodi table
        Schema::table('kaprodi', function (Blueprint $table) {
            $table->foreignId('prodis_id')->nullable()->constrained('prodi')->onDelete('cascade');
        });

        // Copy relations back from pivot to kaprodi table
        $pivots = DB::table('kaprodi_prodi')->get();
        foreach ($pivots as $pivot) {
            DB::table('kaprodi')
                ->where('id', $pivot->kaprodi_id)
                ->update(['prodis_id' => $pivot->prodi_id]);
        }

        // Drop pivot table
        Schema::dropIfExists('kaprodi_prodi');
    }
};
