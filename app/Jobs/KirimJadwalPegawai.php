<?php

namespace App\Jobs;

use App\Models\JadwalUjian;
use App\Models\Pegawai;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;

class KirimJadwalPegawai implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jadwals;
    protected $pegawai;

    public function __construct($jadwals, $pegawai)
    {
        $this->jadwals = $jadwals;
        $this->pegawai = $pegawai;
    }

    public function handle()
    {
        $pesan = "Sistem WA send dari Akademik Polsa.\nMengingatkan jadwal ujian hari ini\n\n";
        
        foreach ($this->jadwals as $jadwal) {
            $pesan .= "📖 *Mata Kuliah:* " . $jadwal->matkul->nama_matkul . "\n" .
                "🏢 *Ruang:* " . $jadwal->ruangan->nama . "\n" .
                "🎓 *Kelas:* " . $jadwal->kelas->nama_kelas . "\n" .
                "⏳ *Waktu:* " . Carbon::parse($jadwal->waktu_mulai)->format('H:i') . " - " . Carbon::parse($jadwal->waktu_selesai)->format('H:i') . "\n\n";
        }

        $pesan .= "Mohon hadir sesuai jadwal. ✅";

        WhatsappService::kirim($this->pegawai->no_telephone, $pesan);

        Log::info('Pesan jadwal ujian terkirim ke pegawai: ' . $this->pegawai->no_telephone);
    }
}

