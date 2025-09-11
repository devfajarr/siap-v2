<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Pegawai;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PegawaiExport implements FromCollection, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Pegawai::select(
            'nama',
            'nuptk',
            'jenis_kelamin',
            'no_telephone',
            'agama',
            'status',
            'tanggal_lahir',
            'tempat_lahir',
            'email'
        )
            ->orderBy('nama', 'asc')
            ->get()
            ->map(function ($pegawai) {
                return [
                    'nama' => $pegawai->nama,
                    'nuptk' => $pegawai->nuptk ? "'" . $pegawai->nuptk : '-',
                    'jenis_kelamin' => $pegawai->jenis_kelamin,
                    'no_telephone' => "'" . $pegawai->no_telephone,
                    'agama' => $pegawai->agama,
                    'status' => $pegawai->status ? 'Aktif' : 'Tidak Aktif',
                    'tanggal_lahir' => $pegawai->tanggal_lahir
                        ? Carbon::parse($pegawai->tanggal_lahir)->translatedFormat('d F Y')
                        : '',
                    'tempat_lahir' => $pegawai->tempat_lahir,
                    'email' => $pegawai->email,
                ];
            });
    }


    public function headings(): array
    {
        return [
            'Nama',
            'NUPTK',
            'Jenis Kelamin',
            'No. Telepon',
            'Agama',
            'Status',
            'Tanggal Lahir',
            'Tempat Lahir',
            'Email',
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
