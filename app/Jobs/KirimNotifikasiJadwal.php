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
        $pesan = "*SIA POLSA NOTIFICATION BOT* 🤖\n"
            ."══════════════════════════\n"
            ."⏰ *PENGINGAT JADWAL KULIAH*\n"
            ."══════════════════════════\n"
            .'• *Mata Kuliah:* '.$this->jadwal->matkul->nama_matkul."\n"
            .'• *Ruangan:* '.$this->jadwal->ruangan->nama."\n"
            .'• *Kelas:* '.$this->jadwal->kelas->nama_kelas."\n"
            .'• *Waktu Mulai:* '.date('H:i', strtotime($this->jadwal->waktu_mulai))." WIB\n\n"
            ."Kelas akan dimulai dalam *15 menit*. Harap bersiap dan hadir tepat waktu. Selamat mengajar! 🎓\n"
            ."──────────────────────────\n"
            .'📅 _Hari Ini: '.now()->translatedFormat('l, d F Y')."_\n"
            ."🌐 _Akses Portal: siapv2.polsa.ac.id_\n"
            .'_SIA POLSA - Sistem Informasi Akademik_';

        WhatsappService::kirim($this->jadwal->dosen->no_telephone, $pesan);
    }
}
