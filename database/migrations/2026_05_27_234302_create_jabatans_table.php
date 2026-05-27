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
        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosens_id')->nullable()->constrained('dosens')->onDelete('cascade');
            $table->foreignId('pegawais_id')->nullable()->constrained('pegawais')->onDelete('cascade');
            $table->string('nama_jabatan'); // e.g. bpmi, kemahasiswaan, perpustakaan, sarpras, personalia
            $table->string('email');
            $table->string('password');
            $table->tinyInteger('status')->default(1); // 1 = Aktif, 0 = Nonaktif
            $table->boolean('is_first_login')->default(true);
            $table->timestamps();

            // A user cannot have the same role assigned multiple times with the same email
            $table->unique(['email', 'nama_jabatan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jabatans');
    }
};
