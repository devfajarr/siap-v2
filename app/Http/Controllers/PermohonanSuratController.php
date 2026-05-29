<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Direktur;
use App\Models\Kaprodi;
use App\Models\Mahasiswa;
use App\Models\NilaiHuruf;
use App\Models\PermohonanSurat;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\Wadir;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PermohonanSuratController extends Controller
{
    protected $userId;

    protected $prodiId;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->userId = Session::get('user.id');
            $this->prodiId = Session::get('user.prodiId');

            return $next($request);
        });
    }

    public function index()
    {
        $mahasiswa = Mahasiswa::with('kelas.semester')->where('id', $this->userId)->first();

        $semesters = NilaiHuruf::where('mahasiswa_id', $this->userId)
            ->select('semester_id')
            ->with('semester')
            ->groupBy('semester_id')
            ->get();

        $tahunAkademik = TahunAkademik::where('status', 1)->first();
        $tahun = explode('/', $tahunAkademik->tahun_akademik);

        $tahun_awal = $tahun[0];
        $tahun_akhir = $tahun[1];

        $semester_awal = $mahasiswa->kelas->semester->semester;
        $semester_akhir = $semester_awal + 1;

        $label_awal = ($semester_awal % 2 == 1) ? '(Ganjil)' : '(Genap)';
        $label_akhir = ($semester_akhir % 2 == 1) ? '(Ganjil)' : '(Genap)';

        $masaCuti = $semester_awal.' '.$label_awal.' & '.$semester_akhir.' '.$label_akhir;

        $kelasAsal = $mahasiswa->kelas->jenis_kelas == 'Reguler' ? 'Pagi' : 'Sore';
        $kelasTujuan = ($kelasAsal == 'Pagi') ? 'Sore' : 'Pagi';

        $permohonans = PermohonanSurat::where('mahasiswa_id', $this->userId)->with('mahasiswa', 'mahasiswa.kelas.prodi')->latest()->paginate(5);

        return view('pages.mahasiswa.permohonan_surat.index', compact('mahasiswa', 'semesters', 'tahun_awal', 'tahun_akhir', 'permohonans', 'masaCuti', 'kelasAsal', 'kelasTujuan'));
    }

    public function store(Request $request)
    {
        $mahasiswa = Mahasiswa::findOrFail($this->userId);
        $prodi_id = Prodi::findOrFail($mahasiswa->kelas->prodi->id);
        $kaprodi = Kaprodi::where('prodis_id', $prodi_id->id)->first();
        if ($request->jenis_permohonan == 'Keterangan Aktif Kuliah') {
            $validateData = $request->validate([
                'namaOrangTua' => 'required|string|max:255',
                'alamatOrangTua' => 'required|string',
                'pekerjaan' => 'required|string|max:255',
                'nip' => 'nullable|numeric',
                'pangkatAtauGolongan' => 'nullable|string|max:255',
                'namaInstansi' => 'required|string|max:255',
                'alamatInstansi' => 'required|string|max:255',
                'keperluan' => 'required|string|max:255',
            ], [
                'namaOrangTua.required' => 'Nama Orang Tua harus diisi.',
                'alamatOrangTua.required' => 'Alamat Orang Tua tidak boleh kosong.',
                'pekerjaan.required' => 'Pekerjaan wajib diisi.',
                'nip.numeric' => 'NIP harus berupa angka.',
                'keperluan.required' => 'Keperluan tidak boleh kosong.',
                // 'pangkatAtauGolongan.required' => 'Pangkat/Golongan harus diisi.',
                'alamatInstansi.required' => 'Alamat Instansi harus diisi.',
                'namaInstansi.required' => 'Nama Instansi harus diisi.',
            ]);

            $success = PermohonanSurat::create([
                'mahasiswa_id' => $this->userId,
                'setuju_kaprodi' => 0,
                'status' => 0,
                'jenis_permohonan' => $request->jenis_permohonan,
                'nama_orang_tua' => $validateData['namaOrangTua'],
                'alamat_orang_tua' => $validateData['alamatOrangTua'],
                'pekerjaan' => $validateData['pekerjaan'],
                'nip' => $validateData['nip'],
                'pangkat_golongan' => $validateData['pangkatAtauGolongan'],
                'nama_instansi' => $validateData['namaInstansi'],
                'alamat_instansi' => $validateData['alamatInstansi'],
                'keperluan' => $validateData['keperluan'],

                'tahun_akademik' => $request->tahunAkademik,
            ]);
            if ($success) {
                session()->forget('selected_form');
                WhatsappService::kirim(
                    $kaprodi->no_telephone,
                    "📢 *Permohonan Surat Baru* 📢\n\n".
                        "📄 Jenis Surat: {$request->jenis_permohonan}\n".
                        "👤 Nama: {$mahasiswa->nama_lengkap}\n".
                        "🎓 NIM: {$mahasiswa->nim}\n\n".
                        '📌 Mohon segera verifikasi. Terima kasih. 🙏'
                );
            } else {
                session()->flash('selected_form', $request->jenisSurat);
            }

            return redirect()->back()->with('success', 'Berhasil mengisi formulir permohonan surat.');
        } elseif ($request->jenis_permohonan == 'Cuti Kuliah') {
            $validateData = $request->validate([
                'alasanCuti' => 'required|string|max:255',
            ], [
                'alasanCuti.required' => 'Alasan Cuti harus diisi.',
            ]);

            $success = PermohonanSurat::create([
                'mahasiswa_id' => $this->userId,
                'setuju_kaprodi' => 0,
                'status' => 0,
                'jenis_permohonan' => $request->jenis_permohonan,
                'masa_cuti' => $request->masaCuti,
                'tahun_akademik' => $request->tahunAkademik,
                'alasan_cuti' => $validateData['alasanCuti'],
            ]);
            if ($success) {
                session()->forget('selected_form');
                WhatsappService::kirim(
                    $kaprodi->no_telephone,
                    "📢 *Permohonan Surat Baru* 📢\n\n".
                        "📄 Jenis Surat: {$request->jenis_permohonan}\n".
                        "👤 Nama: {$mahasiswa->nama_lengkap}\n".
                        "🎓 NIM: {$mahasiswa->nim}\n\n".
                        '📌 Mohon segera verifikasi. Terima kasih. 🙏'
                );
            } else {
                session()->flash('selected_form', $request->jenisSurat);
            }

            return redirect()->back()->with('success', 'Berhasil mengisi formulir permohonan surat.');
        } elseif ($request->jenis_permohonan == 'Tunjangan Gaji') {
            $validateData = $request->validate([
                'namaOrangTua' => 'required|string|max:255',
                'alamatOrangTua' => 'required|string',
                'pekerjaan' => 'required|string|max:255',
                'nip' => 'nullable|numeric',
                'pangkatAtauGolongan' => 'required|string|max:255',
                'namaInstansi' => 'required|string|max:255',
                'alamatInstansi' => 'required|string|max:255',
            ], [
                'namaOrangTua.required' => 'Nama Orang Tua harus diisi.',
                'alamatOrangTua.required' => 'Alamat Orang Tua tidak boleh kosong.',
                'pekerjaan.required' => 'Pekerjaan wajib diisi.',
                'nip.numeric' => 'NIP harus berupa angka.',
                'keperluan.required' => 'Keperluan tidak boleh kosong.',
                'masaStudi.required' => 'Masa Studi harus diisi.',
                'pangkatAtauGolongan.required' => 'Pangkat/Golongan harus diisi.',
                'alamatInstansi.required' => 'Alamat Instansi harus diisi.',
                'namaInstansi.required' => 'Nama Instansi harus diisi.',
                'alasanCuti.required' => 'Alasan Cuti harus diisi.',
            ]);

            $success = PermohonanSurat::create([
                'mahasiswa_id' => $this->userId,
                'setuju_kaprodi' => 0,
                'setuju_mhs' => 1,
                'status' => 0,
                'jenis_permohonan' => $request->jenis_permohonan,
                'nama_orang_tua' => $validateData['namaOrangTua'],
                'alamat_orang_tua' => $validateData['alamatOrangTua'],
                'pekerjaan' => $validateData['pekerjaan'],
                'nip' => $validateData['nip'],
                'pangkat_golongan' => $validateData['pangkatAtauGolongan'],
                'nama_instansi' => $validateData['namaInstansi'],
                'alamat_instansi' => $validateData['alamatInstansi'],
                'tahun_akademik' => $request->tahunAkademik,
            ]);
            if ($success) {
                session()->forget('selected_form');
                WhatsappService::kirim(
                    $kaprodi->no_telephone,
                    "📢 *Permohonan Surat Baru* 📢\n\n".
                        "📄 Jenis Surat: {$request->jenis_permohonan}\n".
                        "👤 Nama: {$mahasiswa->nama_lengkap}\n".
                        "🎓 NIM: {$mahasiswa->nim}\n\n".
                        '📌 Mohon segera verifikasi. Terima kasih. 🙏'
                );
            } else {
                session()->flash('selected_form', $request->jenisSurat);
            }

            return redirect()->back()->with('success', 'Berhasil mengisi formulir permohonan surat.');
        } elseif ($request->jenis_permohonan == 'Pindah Kelas') {
            $validateData = $request->validate([
                'kelasAsal' => 'required|string|max:255',
                'kelasTujuan' => 'required|string',
            ], [
                'kelasAsal.required' => 'Kelas Asal harus diisi.',
                'kelasTujuan.required' => 'Kelas Tujuan harus diisi.',
            ]);

            $success = PermohonanSurat::create([
                'mahasiswa_id' => $this->userId,
                'setuju_kaprodi' => 0,
                'status' => 0,
                'jenis_permohonan' => $request->jenis_permohonan,
                'kelas_asal' => $validateData['kelasAsal'],
                'kelas_tujuan' => $validateData['kelasTujuan'],
            ]);
            if ($success) {
                session()->forget('selected_form');
                WhatsappService::kirim(
                    $kaprodi->no_telephone,
                    "📢 *Permohonan Surat Baru* 📢\n\n".
                        "📄 Jenis Surat: {$request->jenis_permohonan}\n".
                        "👤 Nama: {$mahasiswa->nama_lengkap}\n".
                        "🎓 NIM: {$mahasiswa->nim}\n\n".
                        '📌 Mohon segera verifikasi. Terima kasih. 🙏'
                );
            } else {
                session()->flash('selected_form', $request->jenisSurat);
            }

            return redirect()->back()->with('success', 'Berhasil mengisi formulir permohonan surat.');
        } elseif ($request->jenis_permohonan == 'Pindah PT') {
            $validateData = $request->validate([
                'ptAsal' => 'required|string|max:255',
                'ptTujuan' => 'required|string',
                'statusAkreditasi' => 'required|string',
            ], [
                'ptAsal.required' => 'PT Asal harus diisi.',
                'ptTujuan.required' => 'PT Tujuan harus diisi.',
                'statusAkreditasi.required' => 'Status Akreditasi harus diisi.',
            ]);

            $success = PermohonanSurat::create([
                'mahasiswa_id' => $this->userId,
                'setuju_kaprodi' => 0,
                'status' => 0,
                'jenis_permohonan' => $request->jenis_permohonan,
                'pt_asal' => $validateData['ptAsal'],
                'pt_tujuan' => $validateData['ptTujuan'],
                'status_akreditasi' => $validateData['statusAkreditasi'],
            ]);
            if ($success) {
                session()->forget('selected_form');
                WhatsappService::kirim(
                    $kaprodi->no_telephone,
                    "📢 *Permohonan Surat Baru* 📢\n\n".
                        "📄 Jenis Surat: {$request->jenis_permohonan}\n".
                        "👤 Nama: {$mahasiswa->nama_lengkap}\n".
                        "🎓 NIM: {$mahasiswa->nim}\n\n".
                        '📌 Mohon segera verifikasi. Terima kasih. 🙏'
                );
            } else {
                session()->flash('selected_form', $request->jenisSurat);
            }

            return redirect()->back()->with('success', 'Berhasil mengisi formulir permohonan surat.');
        } elseif ($request->jenis_permohonan == 'Mengundurkan Diri') {
            $success = PermohonanSurat::create([
                'mahasiswa_id' => $this->userId,
                'setuju_kaprodi' => 0,
                'setuju_mhs' => 1,
                'status' => 0,
                'jenis_permohonan' => $request->jenis_permohonan,
            ]);
            if ($success) {
                session()->forget('selected_form');
                WhatsappService::kirim(
                    $kaprodi->no_telephone,
                    "📢 *Permohonan Surat Baru* 📢\n\n".
                        "📄 Jenis Surat: {$request->jenis_permohonan}\n".
                        "👤 Nama: {$mahasiswa->nama_lengkap}\n".
                        "🎓 NIM: {$mahasiswa->nim}\n\n".
                        '📌 Mohon segera verifikasi. Terima kasih. 🙏'
                );
            } else {
                session()->flash('selected_form', $request->jenisSurat);
            }

            return redirect()->back()->with('success', 'Berhasil mengisi formulir permohonan surat.');
        } elseif ($request->jenis_permohonan == 'Ijin Memperoleh Data TA') {
            $validateData = $request->validate([
                'namaInstansi' => 'required|string|max:255',
                'alamatInstansi' => 'required|string|max:255',
                'judulLaporan' => 'required|string|max:255',
                'dataDimintaTA' => 'array|min:1',
                'dataDimintaTA.*' => 'required_without_all:dataDimintaTA.0|string|max:255',
            ], [
                'namaInstansi.required' => 'Nama Instansi harus diisi.',
                'alamatInstansi.required' => 'Alamat Instansi harus diisi.',
                'judulLaporan.required' => 'Judul Laporan harus diisi.',
                'dataDimintaTA.required' => 'Minimal harus ada 1 data yang diminta.',
                'dataDimintaTA.*.required_without_all' => 'Setiap data yang diminta tidak boleh kosong.',
            ]);

            $success = PermohonanSurat::create([
                'mahasiswa_id' => $this->userId,
                'setuju_kaprodi' => 0,
                'setuju_mhs' => 1,
                'status' => 0,
                'jenis_permohonan' => $request->jenis_permohonan,
                'judul_laporan' => $validateData['judulLaporan'],
                'nama_instansi' => $validateData['namaInstansi'],
                'alamat_instansi' => $validateData['alamatInstansi'],
                'data_diminta' => $validateData['dataDimintaTA'],
            ]);
            if ($success) {
                session()->forget('selected_form');
                WhatsappService::kirim(
                    $kaprodi->no_telephone,
                    "📢 *Permohonan Surat Baru* 📢\n\n".
                        "📄 Jenis Surat: {$request->jenis_permohonan}\n".
                        "👤 Nama: {$mahasiswa->nama_lengkap}\n".
                        "🎓 NIM: {$mahasiswa->nim}\n\n".
                        '📌 Mohon segera verifikasi. Terima kasih. 🙏'
                );
            } else {
                session()->flash('selected_form', $request->jenisSurat);
            }

        } elseif ($request->jenis_permohonan == 'Ijin Memperoleh Data PKL') {
            $validateData = $request->validate([
                'namaInstansi' => 'required|string|max:255',
                'alamatInstansi' => 'required|string|max:255',
                'judulLaporan' => 'required|string|max:255',
                'dataDimintaPKL' => 'array|min:1',
                'dataDimintaPKL.*' => 'required_without_all:dataDimintaPKL.0|string|max:255',
            ], [
                'namaInstansi.required' => 'Nama Instansi harus diisi.',
                'alamatInstansi.required' => 'Alamat Instansi harus diisi.',
                'judulLaporan.required' => 'Judul Laporan harus diisi.',
                'dataDimintaPKL.required' => 'Minimal harus ada 1 data yang diminta.',
                'dataDimintaPKL.*.required_without_all' => 'Setiap data yang diminta tidak boleh kosong.',
            ]);

            $success = PermohonanSurat::create([
                'mahasiswa_id' => $this->userId,
                'setuju_kaprodi' => 0,
                'setuju_mhs' => 1,
                'status' => 0,
                'jenis_permohonan' => $request->jenis_permohonan,
                'judul_laporan' => $validateData['judulLaporan'],
                'nama_instansi' => $validateData['namaInstansi'],
                'alamat_instansi' => $validateData['alamatInstansi'],
                'data_diminta' => $validateData['dataDimintaPKL'],
            ]);
            if ($success) {
                session()->forget('selected_form');
                WhatsappService::kirim(
                    $kaprodi->no_telephone,
                    "📢 *Permohonan Surat Baru* 📢\n\n".
                        "📄 Jenis Surat: {$request->jenis_permohonan}\n".
                        "👤 Nama: {$mahasiswa->nama_lengkap}\n".
                        "🎓 NIM: {$mahasiswa->nim}\n\n".
                        '📌 Mohon segera verifikasi. Terima kasih. 🙏'
                );
            } else {
                session()->flash('selected_form', $request->jenisSurat);
            }

            return redirect()->back()->with('success', 'Berhasil mengisi formulir permohonan surat.');
        } elseif ($request->jenis_permohonan == 'Ijin PKL') {

            $validateData = $request->validate([
                'namaInstansi' => 'required|string|max:255',
                'alamatInstansi' => 'required|string|max:255',
                'pimpinan' => 'required|string|max:255',
                'waktuMulai' => 'required',
                'waktuSelesai' => 'required',
            ], [
                'alamatInstansi.required' => 'Alamat Instansi harus diisi.',
                'namaInstansi.required' => 'Nama Instansi harus diisi.',
                'pimpinan.required' => 'Pimpinan harus diisi.',
                'waktuMulai.required' => 'Waktu Mulai PKL harus diisi.',
                'waktuSelesai.required' => 'Waktu Selesai PKL harus diisi.',
            ]);
            $success = PermohonanSurat::create([
                'mahasiswa_id' => $this->userId,
                'setuju_kaprodi' => 0,
                'status' => 0,
                'jenis_permohonan' => $request->jenis_permohonan,
                'pimpinan' => $validateData['pimpinan'],
                'tanggal_mulai' => $validateData['waktuMulai'],
                'tanggal_selesai' => $validateData['waktuSelesai'],
                'nama_instansi' => $validateData['namaInstansi'],
                'alamat_instansi' => $validateData['alamatInstansi'],
            ]);
            if ($success) {
                session()->forget('selected_form');

                WhatsappService::kirim(
                    $kaprodi->no_telephone,
                    "📢 *Permohonan Surat Baru* 📢\n\n".
                        "📄 Jenis Surat: {$request->jenis_permohonan}\n".
                        "👤 Nama: {$mahasiswa->nama_lengkap}\n".
                        "🎓 NIM: {$mahasiswa->nim}\n\n".
                        '📌 Mohon segera verifikasi. Terima kasih. 🙏'
                );
            } else {
                session()->flash('selected_form', $request->jenisSurat);
            }

            return redirect()->back()->with('success', 'Berhasil mengisi formulir permohonan surat.');
        }
    }

    public function update(Request $request, $id)
    {
        $permohonan = PermohonanSurat::findOrFail($id);

        if ($permohonan->jenis_permohonan == 'Keterangan Aktif Kuliah') {
            $validateData = $request->validate([
                'namaOrangTua' => 'required|string|max:255',
                'alamatOrangTua' => 'required|string',
                'pekerjaan' => 'required|string|max:255',
                'nip' => 'nullable|numeric',
                'pangkatAtauGolongan' => 'nullable|string|max:255',
                'namaInstansi' => 'required|string|max:255',
                'alamatInstansi' => 'required|string|max:255',
                'keperluan' => 'required|string|max:255',
            ], [
                'alamatOrangTua.required' => 'Alamat Orang Tua tidak boleh kosong.',
                'pekerjaan.required' => 'Pekerjaan wajib diisi.',
                'nip.numeric' => 'NIP harus berupa angka.',
                'keperluan.required' => 'Keperluan tidak boleh kosong.',
                'alamatInstansi.required' => 'Alamat Instansi harus diisi.',
                'namaInstansi.required' => 'Nama Instansi harus diisi.',
            ]);

            $permohonan->update(
                [
                    'nama_orang_tua' => $validateData['namaOrangTua'],
                    'alamat_orang_tua' => $validateData['alamatOrangTua'],
                    'pekerjaan' => $validateData['pekerjaan'],
                    'nip' => $validateData['nip'],
                    'pangkat_golongan' => $validateData['pangkatAtauGolongan'],
                    'nama_instansi' => $validateData['namaInstansi'],
                    'alamat_instansi' => $validateData['alamatInstansi'],
                    'keperluan' => $validateData['keperluan'],
                ]
            );
        } elseif ($permohonan->jenis_permohonan == 'Cuti Kuliah') {
            $validateData = $request->validate([
                'alasanCuti' => 'required|string|max:255',
            ], [
                'alasanCuti.required' => 'Alasan Cuti harus diisi.',
            ]);

            $permohonan->update(
                [
                    'alasan_cuti' => $validateData['alasanCuti'],
                ]
            );
        } elseif ($permohonan->jenis_permohonan == 'Pindah Kelas') {
            $validateData = $request->validate([
                'kelasAsal' => 'required|string|max:255',
                'kelasTujuan' => 'required|string',
            ], [
                'kelasAsal.required' => 'Kelas Asal harus diisi.',
                'kelasTujuan.required' => 'Kelas Tujuan harus diisi.',
            ]);

            $permohonan->update(
                [
                    'kelas_asal' => $validateData['kelasAsal'],
                    'kelas_tujuan' => $validateData['kelasTujuan'],
                ]
            );
        } elseif ($permohonan->jenis_permohonan == 'Pindah PT') {
            $validateData = $request->validate([
                'ptAsal' => 'required|string|max:255',
                'ptTujuan' => 'required|string',
                'statusAkreditasi' => 'required|string',
            ], [
                'ptAsal.required' => 'PT Asal harus diisi.',
                'ptTujuan.required' => 'PT Tujuan harus diisi.',
                'statusAkreditasi.required' => 'Status Akreditasi harus diisi.',
            ]);

            $permohonan->update(
                [
                    'pt_asal' => $validateData['ptAsal'],
                    'pt_tujuan' => $validateData['ptTujuan'],
                    'status_akreditasi' => $validateData['statusAkreditasi'],
                ]
            );
        } elseif ($permohonan->jenis_permohonan == 'Ijin Memperoleh Data PKL') {
            $validateData = $request->validate([
                'namaInstansi' => 'required|string|max:255',
                'alamatInstansi' => 'required|string|max:255',
                'judulLaporan' => 'required|string|max:255',
                'dataDiminta' => 'required|array|min:1',
                'dataDiminta.*' => 'required|string|max:255',
            ], [
                'alamatInstansi.required' => 'Alamat Instansi harus diisi.',
                'namaInstansi.required' => 'Nama Instansi harus diisi.',
                'judulLaporan.required' => 'Judul Laporan harus diisi.',
                'dataDiminta.required' => 'Minimal harus ada 1 data yang diminta.',
                'dataDiminta.*.required_without_all' => 'Setiap data yang diminta tidak boleh kosong.',
            ]);

            $permohonan->update(
                [
                    'alamat_instansi' => $validateData['alamatInstansi'],
                    'nama_instansi' => $validateData['namaInstansi'],
                    'judul_laporan' => $validateData['judulLaporan'],
                    'data_diminta' => $validateData['dataDiminta'],
                ]
            );
        } elseif ($permohonan->jenis_permohonan == 'Ijin Memperoleh Data TA') {
            $validateData = $request->validate([
                'namaInstansi' => 'required|string|max:255',
                'alamatInstansi' => 'required|string|max:255',
                'judulLaporan' => 'required|string|max:255',
                'dataDiminta' => 'required|array|min:1',
                'dataDiminta.*' => 'required|string|max:255',
            ], [
                'alamatInstansi.required' => 'Alamat Instansi harus diisi.',
                'namaInstansi.required' => 'Nama Instansi harus diisi.',
                'judulLaporan.required' => 'Judul Laporan harus diisi.',
                'dataDiminta.required' => 'Minimal harus ada 1 data yang diminta.',
                'dataDiminta.*.required_without_all' => 'Setiap data yang diminta tidak boleh kosong.',
            ]);

            $permohonan->update(
                [
                    'alamat_instansi' => $validateData['alamatInstansi'],
                    'nama_instansi' => $validateData['namaInstansi'],
                    'judul_laporan' => $validateData['judulLaporan'],
                    'data_diminta' => $validateData['dataDiminta'],
                ]
            );
        } elseif ($permohonan->jenis_permohonan == 'Ijin PKL') {
            $validateData = $request->validate([
                'namaInstansi' => 'required|string|max:255',
                'alamatInstansi' => 'required|string|max:255',
                'pimpinan' => 'required|string|max:255',
                'tanggalMulai' => 'required|date',
                'tanggalSelesai' => 'required|date|after_or_equal:tanggalMulai',
            ], [
                'alamatInstansi.required' => 'Alamat Instansi harus diisi.',
                'namaInstansi.required' => 'Nama Instansi harus diisi.',
                'pimpinan.required' => 'Pimpinan harus diisi.',
                'tanggalMulai.required' => 'Tanggal Mulai PKL harus diisi.',
                'tanggalSelesai.required' => 'Tanggal Selesai PKL harus diisi.',
                'tanggalSelesai.after_or_equal' => 'Tanggal Selesai tidak boleh sebelum Tanggal Mulai.',
            ]);

            $permohonan->update([
                'nama_instansi' => $validateData['namaInstansi'],
                'alamat_instansi' => $validateData['alamatInstansi'],
                'pimpinan' => $validateData['pimpinan'],
                'tanggal_mulai' => $validateData['tanggalMulai'],
                'tanggal_selesai' => $validateData['tanggalSelesai'],
            ]);
        }

        return response()->json(['success' => 'Data Permohonan Surat Berhasil diupdate!']);
    }

    public function delete($id)
    {
        $permohonan = PermohonanSurat::findOrFail($id);
        if ($permohonan) {
            $permohonan->delete();

            return response()->json(['success' => 'Data Permohonan Berhasil dihapus!']);
        }
    }

    public function verify()
    {
        $permohonans = PermohonanSurat::whereHas('mahasiswa', function ($query) {
            $query->whereHas('kelas', function ($query) {
                $query->whereHas('prodi', function ($query) {
                    $query->where('id', $this->prodiId);
                });
            });
        })
            ->with('mahasiswa', 'mahasiswa.kelas.prodi')
            ->where('setuju_kaprodi', 0)
            ->latest()
            ->get();

        return view('pages.permohonan_surat.verifikasi', compact('permohonans'));
    }

    public function verifying(Request $request)
    {
        $permohonan = PermohonanSurat::findOrFail($request->id);
        $nomor_akademik = Admin::pluck('no_telephone')->first();

        if ($permohonan) {
            $permohonan->update(['setuju_kaprodi' => true]);

            WhatsappService::kirim(
                $nomor_akademik,
                "✅ *Verifikasi Permohonan Surat* ✅\n\n".
                    "📄 Jenis Surat: {$request->jenis_permohonan}\n".
                    "👤 Nama Mahasiswa: {$permohonan->mahasiswa->nama_lengkap}\n".
                    "🎓 NIM: {$permohonan->mahasiswa->nim}\n\n".
                    '📌 Permohonan surat ini telah diverifikasi oleh Kaprodi dan siap untuk dicetak'
            );

            return redirect()->back()->with(['success' => 'Data Permohonan Surat Berhasil Diverifikasi!']);
        }
    }

    public function disetujui()
    {
        $permohonans = PermohonanSurat::where('setuju_kaprodi', 1)->where('status', 0)->with('mahasiswa.kelas.prodi', 'mahasiswa.kelas', 'mahasiswa')->latest()->paginate(5);
        $page = 'Cetak';

        return view('pages.permohonan_surat.disetujui', compact('permohonans', 'page'));
    }

    public function cetak(Request $request)
    {
        $permohonan = PermohonanSurat::with('mahasiswa', 'mahasiswa.kelas.prodi', 'mahasiswa.kelas')->findOrFail($request->id);
        $direktur = Direktur::where('status', 1)->first();
        $wadir = Wadir::where('status', 1)->where('no', 1)->first();
        $tahunAkademik = TahunAkademik::where('status', 1)->first();
        $kelas_baru = $this->tukarHurufAB($permohonan->mahasiswa->kelas->nama_kelas);

        if ($permohonan) {
            $permohonan->update(
                [
                    'status' => true,
                    'no_surat' => $request->noSurat,
                ]
            );

            WhatsappService::kirim(
                $permohonan->mahasiswa->no_telephone,
                "📢 *Pemberitahuan Surat Permohonan* 📢\n\n".
                    "📄 Jenis Surat: {$permohonan->jenis_permohonan}\n".
                    "📌 Status: *Segera Dicetak* ✅\n\n".
                    '📍 Mohon segera menghubungi akademik untuk pengambilan surat. Terima kasih. 🙏'
            );

            if ($permohonan->jenis_permohonan == 'Pindah PT') {
                return view('pages.permohonan_surat.pindah_pt', compact('permohonan', 'direktur', 'tahunAkademik'));
            } elseif ($permohonan->jenis_permohonan == 'Keterangan Aktif Kuliah') {
                return view('pages.permohonan_surat.aktif_kuliah', compact('permohonan', 'direktur', 'tahunAkademik'));
            } elseif ($permohonan->jenis_permohonan == 'Ijin PKL') {
                return view('pages.permohonan_surat.ijin_pkl', compact('permohonan', 'direktur', 'tahunAkademik'));
            } elseif ($permohonan->jenis_permohonan == 'Pindah Kelas') {
                return view('pages.permohonan_surat.pindah_kelas', compact('permohonan', 'direktur', 'tahunAkademik', 'kelas_baru'));
            } elseif ($permohonan->jenis_permohonan == 'Mengundurkan Diri') {
                return view('pages.permohonan_surat.mengundurkan_diri', compact('permohonan', 'wadir', 'tahunAkademik'));
            } elseif ($permohonan->jenis_permohonan == 'Cuti Kuliah') {
                return view('pages.permohonan_surat.cuti_kuliah', compact('permohonan', 'wadir', 'tahunAkademik'));
            } elseif ($permohonan->jenis_permohonan == 'Ijin Memperoleh Data PKL' || $permohonan->jenis_permohonan == 'Ijin Memperoleh Data TA') {
                return view('pages.permohonan_surat.ijin_memperoleh_data', compact('permohonan', 'direktur', 'tahunAkademik'));
            }
        }
    }

    public function selesai()
    {
        $permohonans = PermohonanSurat::where('setuju_kaprodi', 1)
            ->where('status', 1)
            ->with('mahasiswa.kelas.prodi', 'mahasiswa.kelas', 'mahasiswa')
            ->orderByDesc('updated_at')
            ->paginate(5);

        $page = 'Selesai';

        return view('pages.permohonan_surat.disetujui', compact('permohonans', 'page'));
    }

    public function tukarHurufAB($kelas)
    {
        $huruf_terakhir = substr($kelas, -1);

        if ($huruf_terakhir === 'A') {
            $huruf_baru = 'B';
        } elseif ($huruf_terakhir === 'B') {
            $huruf_baru = 'A';
        } else {
            return $kelas;
        }

        return $this->pisahDuaHurufTerakhir(substr($kelas, 0, -1).$huruf_baru);
    }

    public function pisahDuaHurufTerakhir($kelas)
    {
        if (strlen($kelas) >= 3) {
            $dua_terakhir = substr($kelas, -2);
            $bagian_awal = substr($kelas, 0, -2);

            return $bagian_awal.' '.$dua_terakhir;
        }

        return $kelas;
    }
}
