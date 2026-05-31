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
        $pesan = "*SIA POLSA NOTIFICATION BOT* 🤖\n"
            ."══════════════════════════\n"
            ."⏰ *PENGINGAT PENGAWASAN UJIAN*\n"
            ."══════════════════════════\n"
            ."Berikut adalah jadwal pengawasan ujian Anda hari ini:\n\n";

        $i = 1;
        foreach ($this->jadwals as $jadwal) {
            $pesan .= "[{$i}] *".$jadwal->matkul->nama_matkul."*\n"
                .'    • Waktu: '.Carbon::parse($jadwal->waktu_mulai)->format('H:i').' - '.Carbon::parse($jadwal->waktu_selesai)->format('H:i')." WIB\n"
                .'    • Ruang: '.$jadwal->ruangan->nama."\n"
                .'    • Kelas: '.$jadwal->kelas->nama_kelas."\n\n";
            $i++;
        }

        $pesan .= "Jadwal pengawasan ujian akan dimulai dalam *15 menit*. Mohon segera bersiap menuju ruangan ujian.\n"
            ."──────────────────────────\n"
            .'📅 _Hari Ini: '.now()->translatedFormat('l, d F Y')."_\n"
            ."🌐 _Akses Portal: siapv2.polsa.ac.id_\n"
            .'_SIA POLSA - Sistem Informasi Akademik_';

        WhatsappService::kirim($this->pengawas->no_telephone, $pesan);

        Log::info('Pesan jadwal ujian terkirim ke pengawas: '.$this->pengawas->no_telephone);
    }
}
