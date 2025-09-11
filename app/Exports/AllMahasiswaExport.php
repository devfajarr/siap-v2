<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllMahasiswaExport implements FromCollection,  WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Mahasiswa::select(
            'nim',
            'nama_lengkap',
            'tahun_masuk',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'nik',
            'nisn',
            'email',
            'no_telephone',
            'nama_ibu',
            'kelas_id',
            'status_krs',
            'alamat'
        )
        ->with('kelas')
        ->orderBy('nim', 'asc')
        ->get()
        ->map(function ($mahasiswa) {
            return [
                'nim' => $mahasiswa->nim,
                'nama_lengkap' => $mahasiswa->nama_lengkap,
                'tahun_masuk'=> $mahasiswa->tahun_masuk,
                'tempat_lahir' => $mahasiswa->tempat_lahir,
                'tanggal_lahir' => $mahasiswa->tanggal_lahir
                    ? Carbon::parse($mahasiswa->tanggal_lahir)->translatedFormat('d F Y')
                    : '',
                'jenis_kelamin' => $mahasiswa->jenis_kelamin,
                'nik' => "'" . $mahasiswa->nik,
                'nisn' => "'" . $mahasiswa->nisn,
                'email' => $mahasiswa->email,
                'no_telephone' => $mahasiswa->no_telephone,
                'nama_ibu' => $mahasiswa->nama_ibu,
                'kelas' => optional($mahasiswa->kelas)->nama_kelas,
                'status_krs' => $mahasiswa->status_krs ? 'Aktif' : 'Tidak Aktif',
                'alamat' => $mahasiswa->alamat,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Lengkap',
            'Tahun Masuk',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'NIK',
            'NISN',
            'Email',
            'No. Telepon',
            'Nama Ibu',
            'Kelas',
            'Status KRS',
            'Alamat'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'B0BEC5'],
                ],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
    }
