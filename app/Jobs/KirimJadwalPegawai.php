<?php

namespace App\Jobs;

use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class KirimJadwalPegawai implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jadwals;

    protected $pengawas;

    public function __construct($jadwals, $pengawas)
    {
        $this->jadwals = $jadwals;
        $this->pengawas = $pengawas;
    }

    public function handle()
    {
        $pesan = "Sistem WA send dari Akademik Polsa.\nMengingatkan jadwal ujian hari ini\n\n";

        foreach ($this->jadwals as $jadwal) {
            $pesan .= '📖 *Mata Kuliah:* '.$jadwal->matkul->nama_matkul."\n".
                '🏢 *Ruang:* '.$jadwal->ruangan->nama."\n".
                '🎓 *Kelas:* '.$jadwal->kelas->nama_kelas."\n".
                '⏳ *Waktu:* '.Carbon::parse($jadwal->waktu_mulai)->format('H:i').' - '.Carbon::parse($jadwal->waktu_selesai)->format('H:i')."\n\n";
        }

        $pesan .= 'Mohon hadir sesuai jadwal. ✅';

        WhatsappService::kirim($this->pengawas->no_telephone, $pesan);

        Log::info('Pesan jadwal ujian terkirim ke pengawas: '.$this->pengawas->no_telephone);
    }
}
