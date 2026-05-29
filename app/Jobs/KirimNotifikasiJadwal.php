<?php

namespace App\Jobs;

use App\Models\Jadwal;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class KirimNotifikasiJadwal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jadwal;

    public function __construct(Jadwal $jadwal)
    {
        $this->jadwal = $jadwal;
    }

    public function handle()
    {
        $pesan = "🔔 *Pengingat Jadwal Perkuliahan*\n\n".
         '📖 *Mata Kuliah:* '.$this->jadwal->matkul->nama_matkul."\n".
         '🏢 *Ruang:* '.$this->jadwal->ruangan->nama."\n".
         '🎓 *Kelas:* '.$this->jadwal->kelas->nama_kelas."\n".
         '⏳ *Waktu Mulai:* '.date('H:i', strtotime($this->jadwal->waktu_mulai))."\n\n".
         'Harap bersiap dan hadir tepat waktu. Terima kasih. ✅';

        WhatsappService::kirim($this->jadwal->dosen->no_telephone, $pesan);
    }
}
