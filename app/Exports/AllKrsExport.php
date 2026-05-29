<?php

namespace App\Exports;

use App\Models\Krs;
use App\Models\TahunAkademik;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllKrsExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        $aktifTahunAjaran = TahunAkademik::where('status', 1)->pluck('tahun_akademik');

        return Krs::select(
            'mahasiswa_id',
            'prodi_id',
            'semester_id',
            'kelas_id',
            'status_krs',
            'tahun_ajaran'
        )
            ->whereIn('tahun_ajaran', $aktifTahunAjaran)
            ->with(['mahasiswa', 'prodi', 'semester', 'kelas'])
            ->orderBy('tahun_ajaran', 'desc')
            ->get()
            ->map(function ($krs) {
                return [
                    'nama_mahasiswa' => optional($krs->mahasiswa)->nama_lengkap,
                    'nim' => optional($krs->mahasiswa)->nim,
                    'prodi' => optional($krs->prodi)->nama_prodi,
                    'semester' => optional($krs->semester)->semester,
                    'kelas' => optional($krs->kelas)->nama_kelas,
                    'status_krs' => $krs->status_krs ? 'Ya' : 'Tidak',
                    'tahun_ajaran' => $krs->tahun_ajaran,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama Mahasiswa',
            'NIM',
            'Program Studi',
            'Semester',
            'Kelas',
            'Status KRS',
            'Tahun Akademik',
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
