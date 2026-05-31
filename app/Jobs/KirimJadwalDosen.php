<?php

namespace App\Jobs;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class KirimJadwalDosen implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jadwals; // Semua jadwal untuk satu dosen

    protected $dosen;

    public function __construct($jadwals, $dosen)
    {
        $this->jadwals = $jadwals;
        $this->dosen = $dosen;
    }

    public function handle()
    {
        $pesan = "*SIA POLSA NOTIFICATION BOT* 🤖\n"
            ."══════════════════════════\n"
            ."📅 *AGENDA MENGAJAR HARI INI*\n"
            ."══════════════════════════\n"
            ."Berikut adalah rangkuman jadwal mengajar Anda untuk hari ini:\n\n";

        $i = 1;
        foreach ($this->jadwals as $jadwal) {
            $pesan .= "[{$i}] *".$jadwal->matkul->nama_matkul."*\n"
                .'    • Waktu: '.date('H:i', strtotime($jadwal->waktu_mulai)).' - '.date('H:i', strtotime($jadwal->waktu_selesai))." WIB\n"
                .'    • Ruang: '.$jadwal->ruangan->nama."\n"
                .'    • Kelas: '.$jadwal->kelas->nama_kelas."\n\n";
            $i++;
        }

        $pesan .= "Mohon kehadiran Bapak/Ibu Dosen sesuai dengan jadwal di atas.\n"
            ."──────────────────────────\n"
            .'📅 _Hari Ini: '.now()->translatedFormat('l, d F Y')."_\n"
            ."🌐 _Akses Portal: siapv2.polsa.ac.id_\n"
            .'_SIA POLSA - Sistem Informasi Akademik_';

        WhatsappService::kirim($this->dosen->no_telephone, $pesan);
    }
}
