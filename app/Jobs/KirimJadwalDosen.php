<?php

namespace App\Jobs;

use App\Models\Jadwal;
use App\Models\Dosen;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
        $pesan = "Sistem WA send dari Akademik Polsa.\nMengingatkan jadwal perkuliahan hari ini\n\n";
        foreach ($this->jadwals as $jadwal) {
            $pesan .= "📖 *Mata Kuliah:* " . $jadwal->matkul->nama_matkul . "\n" .
                "🏢 *Ruang:* " . $jadwal->ruangan->nama . "\n" .
                "🎓 *Kelas:* " . $jadwal->kelas->nama_kelas . "\n" .
                "⏳ *Waktu:* " . date('H:i', strtotime($jadwal->waktu_mulai)) . " - " . date('H:i', strtotime($jadwal->waktu_selesai)) . "\n\n";
        }
        $pesan .= "Mohon hadir sesuai jadwal. ✅";
        WhatsappService::kirim($this->dosen->no_telephone, $pesan);
    }
}
