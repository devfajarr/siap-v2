<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\OrangTua;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrangTuaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // Fetch students
        $studentAditya = Mahasiswa::where('nim', '241010001')->first();
        $studentBima = Mahasiswa::where('nim', '241010002')->first();

        if ($studentAditya) {
            $ortuAditya = OrangTua::updateOrCreate(
                ['username' => 'ortu.aditya'],
                [
                    'nama' => 'Supriyadi (Orang Tua Aditya)',
                    'password' => $defaultPassword,
                    'no_telephone' => '081234567890',
                    'alamat' => 'Jl. Pahlawan No. 1, Purworejo',
                ]
            );

            $ortuAditya->mahasiswas()->syncWithoutDetaching([
                $studentAditya->id => ['relationship_type' => 'Ayah'],
            ]);
        }

        if ($studentBima) {
            $ortuBima = OrangTua::updateOrCreate(
                ['username' => 'ortu.bima'],
                [
                    'nama' => 'Hartono (Orang Tua Bima)',
                    'password' => $defaultPassword,
                    'no_telephone' => '081234567891',
                    'alamat' => 'Jl. Pahlawan No. 2, Purworejo',
                ]
            );

            $ortuBima->mahasiswas()->syncWithoutDetaching([
                $studentBima->id => ['relationship_type' => 'Ayah'],
            ]);
        }

        if ($studentAditya && $studentBima) {
            $ortuMulti = OrangTua::updateOrCreate(
                ['username' => 'ortu.multichild'],
                [
                    'nama' => 'Siti Aminah (Orang Tua Aditya & Bima)',
                    'password' => $defaultPassword,
                    'no_telephone' => '081234567892',
                    'alamat' => 'Jl. Merdeka No. 10, Purworejo',
                ]
            );

            $ortuMulti->mahasiswas()->syncWithoutDetaching([
                $studentAditya->id => ['relationship_type' => 'Ibu'],
                $studentBima->id => ['relationship_type' => 'Ibu'],
            ]);
        }
    }
}
