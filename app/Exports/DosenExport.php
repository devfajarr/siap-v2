<?php

namespace App\Exports;
use Carbon\Carbon;
use App\Models\Dosen;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DosenExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return Dosen::select(
            'nama',
            'nidn',
            'pembimbing_akademik',
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
            ->map(function ($dosen) {
                return [
                    'nama' => $dosen->nama,
                    'nidn' => $dosen->nidn ? "'" . $dosen->nidn : '-',
                    'pembimbing_akademik' => $dosen->pembimbing_akademik == 1 ? 'Ya' : 'Tidak', 
                    'jenis_kelamin' => $dosen->jenis_kelamin,
                    'no_telephone' => "'" . $dosen->no_telephone,
                    'agama' => $dosen->agama,
                    'status' => $dosen->status ? 'Aktif' : 'Tidak Aktif',
                    'tanggal_lahir' => $dosen->tanggal_lahir
                        ? Carbon::parse($dosen->tanggal_lahir)->translatedFormat('d F Y')
                        : '',
                    'tempat_lahir' => $dosen->tempat_lahir,
                    'email' => $dosen->email,
                ];
            });
    }


    public function headings(): array
    {
        return [
            'Nama',
            'NUPTK',
            'Pembimbing Akademik',
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
