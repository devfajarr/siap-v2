@extends('layouts.main')

@section('container')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="breadcrumb">
                <a href="/presensi/dashboard" class="breadcrumb-item">
                    <span class="mdi mdi-home"></span> Dashboard
                </a>
                <p class="breadcrumb-item">Permohonan Surat</p>
                <p class="breadcrumb-item">{{ $page }}</p>
            </div>
            <div class="row">
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama Mahasiswa</th>
                                            <th>Kelas</th>
                                            <th>NIM</th>
                                            <th>Jenis Permohonan</th>
                                            <th>Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($permohonans as $permohonan)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $permohonan->mahasiswa->nama_lengkap }}</td>
                                                <td>{{ $permohonan->mahasiswa->kelas->nama_kelas }}</td>
                                                <td>{{ $permohonan->mahasiswa->nim }}</td>
                                                <td>{{ $permohonan->jenis_permohonan }}</td>
                                                <td>

                                                    <button data-bs-toggle="modal"
                                                        @if ($permohonan->jenis_permohonan == 'Keterangan Aktif Kuliah') data-bs-target="#aktifKuliahShowModal" class="keteranganAktifEdit btn btn-sm btn-warning"
                                                    data-id="{{ $permohonan->id }}"
                                                    data-nama-orang-tua="{{ $permohonan->mahasiswa->nama_ibu }}"
                                                    data-nama="{{ $permohonan->mahasiswa->nama_lengkap }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-prodi="{{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-alamat-orang-tua="{{ $permohonan->alamat_orang_tua }}"
                                                    data-tempat-tgl-lahir="{{ $permohonan->mahasiswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($permohonan->mahasiswa->tanggal_lahir)->translatedFormat('d F Y') }}"
                                                    data-alamat="{{ $permohonan->mahasiswa->alamat }}"
                                                    data-telp="{{ $permohonan->mahasiswa->no_telephone }}"
                                                    data-pekerjaan="{{ $permohonan->pekerjaan }}"
                                                    data-nip="{{ $permohonan->nip }}"
                                                    data-pangkat-atau-golongan="{{ $permohonan->pangkat_golongan }}"
                                                    data-nama-instansi="{{ $permohonan->nama_instansi }}"
                                                    data-alamat-instansi="{{ $permohonan->alamat_instansi }}"
                                                    data-keperluan="{{ $permohonan->keperluan }}"
                                                    data-masa-studi="{{ $permohonan->masa_studi }}"
                                                    data-alasan-cuti="{{ $permohonan->alasan_cuti }}"
                                                    data-tahu-akademik="{{ $permohonan->tahun_akademik }}"

                                                    @elseif ($permohonan->jenis_permohonan == 'Cuti Kuliah')
                                                    data-bs-target="#cutiKuliahShowModal" class="cutiKuliahEdit btn btn-sm btn-warning"
                                                    data-id="{{ $permohonan->id }}"
                                                    data-nama-orang-tua="{{ $permohonan->mahasiswa->nama_ibu }}"
                                                    data-nama="{{ $permohonan->mahasiswa->nama_lengkap }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-prodi="{{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-alamat-orang-tua="{{ $permohonan->alamat_orang_tua }}"
                                                    data-tempat-tgl-lahir="{{ $permohonan->mahasiswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($permohonan->mahasiswa->tanggal_lahir)->translatedFormat('d F Y') }}"
                                                    data-alamat="{{ $permohonan->mahasiswa->alamat }}"
                                                    data-telp="{{ $permohonan->mahasiswa->no_telephone }}"
                                                    data-pekerjaan="{{ $permohonan->pekerjaan }}"
                                                    data-nip="{{ $permohonan->nip }}"
                                                    data-pangkat-atau-golongan="{{ $permohonan->pangkat_golongan }}"
                                                    data-nama-instansi="{{ $permohonan->nama_instansi }}"
                                                    data-alamat-instansi="{{ $permohonan->alamat_instansi }}"
                                                    data-keperluan="{{ $permohonan->keperluan }}"
                                                    data-masa-cuti="{{ $permohonan->masa_cuti }}"
                                                    data-alasan-cuti="{{ $permohonan->alasan_cuti }}"
                                                    data-tahu-akademik="{{ $permohonan->tahun_akademik }}"

                                                    @elseif ($permohonan->jenis_permohonan == 'Mengundurkan Diri')
                                                    data-bs-target="#mengundurkanDiriShowModal" class="mengundurkanDiriEdit btn btn-sm btn-warning"
                                                    data-id="{{ $permohonan->id }}"
                                                    data-nama="{{ $permohonan->mahasiswa->nama_lengkap }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-prodi="{{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-tempat-tgl-lahir="{{ $permohonan->mahasiswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($permohonan->mahasiswa->tanggal_lahir)->translatedFormat('d F Y') }}"
                                                    data-alamat="{{ $permohonan->mahasiswa->alamat }}"
                                                    data-telp="{{ $permohonan->mahasiswa->no_telephone }}"


                                                    @elseif ($permohonan->jenis_permohonan == 'Perpanjangan Studi')
                                                    data-bs-target="#perpanjanganStudiShowModal" class="perpanjanganStudiEdit btn btn-sm btn-warning"
                                                    data-id="{{ $permohonan->id }}"
                                                    data-nama-orang-tua="{{ $permohonan->mahasiswa->nama_ibu }}"
                                                    data-nama="{{ $permohonan->mahasiswa->nama_lengkap }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-prodi="{{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-alamat-orang-tua="{{ $permohonan->alamat_orang_tua }}"
                                                    data-tempat-tgl-lahir="{{ $permohonan->mahasiswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($permohonan->mahasiswa->tanggal_lahir)->translatedFormat('d F Y') }}"
                                                    data-alamat="{{ $permohonan->mahasiswa->alamat }}"
                                                    data-telp="{{ $permohonan->mahasiswa->no_telephone }}"
                                                    data-pekerjaan="{{ $permohonan->pekerjaan }}"
                                                    data-nip="{{ $permohonan->nip }}"
                                                    data-pangkat-atau-golongan="{{ $permohonan->pangkat_golongan }}"
                                                    data-nama-instansi="{{ $permohonan->nama_instansi }}"
                                                    data-alamat-instansi="{{ $permohonan->alamat_instansi }}"
                                                    data-keperluan="{{ $permohonan->keperluan }}"
                                                    data-masa-studi="{{ $permohonan->masa_studi }}"
                                                    data-alasan-cuti="{{ $permohonan->alasan_cuti }}"
                                                    data-tahu-akademik="{{ $permohonan->tahun_akademik }}"

                                                    @elseif($permohonan->jenis_permohonan == 'Pindah Kelas')
                                                    data-bs-target = '#pindahKelasShowModal' class="pindahKelasEdit btn btn-sm btn-warning"
                                                    data-id ="{{ $permohonan->id }}"
                                                    data-kelas-asal="{{ $permohonan->kelas_asal }}"
                                                    data-kelas-tujuan="{{ $permohonan->kelas_tujuan }}"
                                                    data-nama="{{ $permohonan->mahasiswa->nama_lengkap }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-prodi="{{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-alamat-orang-tua="{{ $permohonan->alamat_orang_tua }}"
                                                    data-tempat-tgl-lahir="{{ $permohonan->mahasiswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($permohonan->mahasiswa->tanggal_lahir)->translatedFormat('d F Y') }}"
                                                    data-alamat="{{ $permohonan->mahasiswa->alamat }}"
                                                    data-telp="{{ $permohonan->mahasiswa->no_telephone }}"

                                                    @elseif($permohonan->jenis_permohonan == 'Pindah PT')
                                                    data-bs-target = '#pindahPTShowModal' class="pindahPTEdit btn btn-sm btn-warning"
                                                    data-id ="{{ $permohonan->id }}"
                                                    data-pt-asal="{{ $permohonan->pt_asal }}"
                                                    data-pt-tujuan="{{ $permohonan->pt_tujuan }}"
                                                    data-nama="{{ $permohonan->mahasiswa->nama_lengkap }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-prodi="{{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-alamat-orang-tua="{{ $permohonan->alamat_orang_tua }}"
                                                    data-tempat-tgl-lahir="{{ $permohonan->mahasiswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($permohonan->mahasiswa->tanggal_lahir)->translatedFormat('d F Y') }}"
                                                    data-alamat="{{ $permohonan->mahasiswa->alamat }}"
                                                    data-telp="{{ $permohonan->mahasiswa->no_telephone }}"

                                                    @elseif(
                                                        $permohonan->jenis_permohonan == 'Ijin Memperoleh Data TA' ||
                                                            $permohonan->jenis_permohonan == 'Ijin Memperoleh Data PKL')
                                                    @php
                                                    $Label = $permohonan->jenis_permohonan;
                                                    $lastWord = Str::afterLast($Label, ' ');
                                                    @endphp

                                                    data-bs-target = '#ijinShowModal' class="ijinEdit btn btn-sm btn-warning"
                                                    data-id ={{ $permohonan->id }}
                                                    data-nama-instansi="{{ $permohonan->nama_instansi }}"
                                                    data-alamat-instansi="{{ $permohonan->alamat_instansi }}"
                                                    data-label="{{ $lastWord }}"
                                                    data-judul-laporan="{{ $permohonan->judul_laporan }}"
                                                    data-nama="{{ $permohonan->mahasiswa->nama_lengkap }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-prodi="{{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-alamat-orang-tua="{{ $permohonan->alamat_orang_tua }}"
                                                    data-tempat-tgl-lahir="{{ $permohonan->mahasiswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($permohonan->mahasiswa->tanggal_lahir)->translatedFormat('d F Y') }}"
                                                    data-alamat="{{ $permohonan->mahasiswa->alamat }}"
                                                    data-telp="{{ $permohonan->mahasiswa->no_telephone }}"
                                                    data-diminta='@json($permohonan->data_diminta)'

                                                    @elseif($permohonan->jenis_permohonan == 'Ijin PKL')
                                                     data-id ="{{ $permohonan->id }}"
                                                    data-bs-target = '#ijinPKLShowModal' class="ijinPKLEdit btn btn-sm btn-warning"
                                                    data-nama-instansi="{{ $permohonan->nama_instansi }}"
                                                    data-alamat-instansi="{{ $permohonan->alamat_instansi }}"
                                                    data-nama="{{ $permohonan->mahasiswa->nama_lengkap }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-prodi="{{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}"
                                                    data-nim="{{ $permohonan->mahasiswa->nim }}"
                                                    data-tempat-tgl-lahir="{{ $permohonan->mahasiswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($permohonan->mahasiswa->tanggal_lahir)->translatedFormat('d F Y') }}"
                                                    data-alamat="{{ $permohonan->mahasiswa->alamat }}"
                                                    data-telp="{{ $permohonan->mahasiswa->no_telephone }}"
                                                    data-waktu="{{ \Carbon\Carbon::parse($permohonan->tanggal_mulai)->translatedFormat('d F Y') }} sampai {{ \Carbon\Carbon::parse($permohonan->tanggal_selesai)->translatedFormat('d F Y') }}"
                                                    data-pimpinan="{{ $permohonan->pimpinan }}" @endif>
                                                        <i class="mdi mdi-eye"></i> Lihat
                                                    </button>

                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" colspan="9">Belum ada permohonan surat</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="container">
                            {{ $permohonans->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- AKTIFK KULIAH --}}
    <div class="modal fade" id="aktifKuliahShowModal" tabindex="-1" aria-labelledby="aktifKuliahShowModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="aktifKuliahShowModalLabel">Verifikasi Data Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Nama</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span id="namaAktif"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">NIM</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span id="nimAktif"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Program Studi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span id="prodiAktif"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Tempat, tgl lahir</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span id="ttlAktif"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alamat</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span id="alamatAktif"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">No. Tlp</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span id="tlpAktif"></span>
                        </div>
                    </div>

                    <div class="mt-3 mb-3 fw-bold">Surat Keterangan Aktif Kuliah</div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Nama Orang Tua</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="namaOrangTuaAktif"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alamat Orang Tua</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="alamatOrangTuaAktif"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Pekerjaan</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="pekerjaanAktif"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">NIP</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span id="nipAktif"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Pangkat/Golongan</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="pangkatAtauGolonganAktif"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Nama Instansi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="namaInstansiAktif"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alamat Instansi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="alamatInstansiAktif"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Keperluan</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="keperluanAktif"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Tahun Akademik</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="tahunAkademikAktif"></span></div>
                    </div>

                    <form action="{{ route('permohonan-surat-disetujui-update') }}" method="POST" target="_blank">
                        @method('PUT')
                        @csrf
                        <input type="hidden" id="aktifKuliahIdEdit" name="id">

                        <div class="mb-3">
                            <label for="noSurat" class="form-label fw-bold mt-2">Nomor Surat</label>
                            <input type="text" class="form-control form-control-sm"
                                placeholder="Contoh : 335/AP.08/DIR/XI/{{ \Carbon\Carbon::parse(now())->format('Y') }}"
                                id="noSurat" name="noSurat" required>
                        </div>

                        <button type="submit" class="mt-4 btn btn-success btn-sm">
                            <i class="mdi mdi-printer-outline"></i> Cetak
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- CUTI KULIAH --}}
    <div class="modal fade" id="cutiKuliahShowModal" tabindex="-1" aria-labelledby="cutiKuliahShowModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cutiKuliahShowModalLabel">Verifikasi Data Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Nama</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="namaCuti"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">NIM</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="nimCuti"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Program Studi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="prodiCuti"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Tempat, tgl lahir</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="ttlCuti"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alamat</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="alamatCuti"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">No. Tlp</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="tlpCuti"></span></div>
                    </div>

                    <div class="mt-3 mb-3 fw-bold">Surat Keterangan Cuti Kuliah</div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Tahun Akademik</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="tahunAkademikCuti"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alasan Cuti</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="alasanCutiCuti"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Masa Cuti</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="masaCuti"></span></div>
                    </div>


                    <form action="{{ route('permohonan-surat-disetujui-update') }}" method="POST" target="_blank">
                        @method('PUT')
                        @csrf
                        <input type="hidden" id="cutiKuliahIdEdit" name="id">

                        <div class="mb-3">
                            <label for="noSurat" class="form-label fw-bold mt-2">Nomor Surat</label>
                            <input type="text" class="form-control form-control-sm"
                                placeholder="Contoh : 335/AP.08/DIR/XI/{{ \Carbon\Carbon::parse(now())->format('Y') }}"
                                id="noSurat" name="noSurat" required>
                        </div>

                        <button type="submit" class="mt-4 btn btn-success btn-sm">
                            <i class="mdi mdi-printer-outline"></i> Cetak
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- PINDAH KELAS --}}
    <div class="modal fade" id="pindahKelasShowModal" tabindex="-1" aria-labelledby="pindahKelasShowModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pindahKelasShowModalLabel">Verifikasi Data Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Nama</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span>
                            <span id="namaPindahKelas"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">NIM</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="nimPindahKelas"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Program Studi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="prodiPindahKelas"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Tempat, tgl lahir</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="ttlPindahKelas"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alamat</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="alamatPindahKelas"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">No. Tlp</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="tlpPindahKelas"></span></div>
                    </div>

                    <div class="mt-3 mb-3 fw-bold">Surat Keterangan Pindah Kelas</div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Kelas Asal</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="kelasAsal"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Kelas Tujuan</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="kelasTujuan"></span></div>
                    </div>
                    <form action="{{ route('permohonan-surat-disetujui-update') }}" method="POST" target="_blank">
                        @method('PUT')
                        @csrf
                        <input type="hidden" id="pindahKelasIdEdit" name="id">

                        <div class="mb-3">
                            <label for="noSurat" class="form-label fw-bold mt-2">Nomor Surat</label>
                            <input type="text" class="form-control form-control-sm"
                                placeholder="Contoh : 335/AP.08/DIR/XI/{{ \Carbon\Carbon::parse(now())->format('Y') }}"
                                id="noSurat" name="noSurat" required>
                        </div>

                        <button type="submit" class="mt-4 btn btn-success btn-sm">
                            <i class="mdi mdi-printer-outline"></i> Cetak
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- PINDAH PT --}}
    <div class="modal fade" id="pindahPTShowModal" tabindex="-1" aria-labelledby="pindahPTShowModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pindahPTShowModalLabel">Verifikasi Data Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Nama</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="namaPindahPT"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">NIM</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="nimPindahPT"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Program Studi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="prodiPindahPT"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Tempat, tgl lahir</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="ttlPindahPT"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alamat</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="alamatPindahPT"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">No. Tlp</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="tlpPindahPT"></span></div>
                    </div>

                    <div class="mt-3 mb-3 fw-bold">Surat Keterangan Pindah PT</div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">PT Asal</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="ptAsal"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">PT Tujuan</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="ptTujuan"></span></div>
                    </div>

                    <form action="{{ route('permohonan-surat-disetujui-update') }}" method="POST" target="_blank">
                        @method('PUT')
                        @csrf
                        <input type="hidden" id="pindahPTIdEdit" name="id">

                        <div class="mb-3">
                            <label for="noSurat" class="form-label fw-bold mt-2">Nomor Surat</label>
                            <input type="text" class="form-control form-control-sm"
                                placeholder="Contoh : 335/AP.08/DIR/XI/{{ \Carbon\Carbon::parse(now())->format('Y') }}"
                                id="noSurat" name="noSurat" required>
                        </div>

                        <button type="submit" class="mt-4 btn btn-success btn-sm">
                            <i class="mdi mdi-printer-outline"></i> Cetak
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MENGUNDURKAN DIRI --}}
    <div class="modal fade" id="mengundurkanDiriShowModal" tabindex="-1"
        aria-labelledby="mengundurkanDiriShowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mengundurkanDiriShowModalLabel">Verifikasi Data Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Nama</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="namaMengundurkanDiri"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">NIM</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="nimMengundurkanDiri"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Program Studi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="prodiMengundurkanDiri"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Tempat, tgl lahir</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="ttlMengundurkanDiri"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alamat</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="alamatMengundurkanDiri"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">No. Tlp</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="tlpMengundurkanDiri"></span></div>
                    </div>

                    <div class="mt-3 fw-bold">Surat Mengundurkan Diri</div>

                    <form action="{{ route('permohonan-surat-disetujui-update') }}" method="POST" target="_blank">
                        @method('PUT')
                        @csrf
                        <input type="hidden" id="mengundurkanDiriIdEdit" name="id">

                        <div class="mb-3">
                            <label for="noSurat" class="form-label fw-bold mt-2">Nomor Surat</label>
                            <input type="text" class="form-control form-control-sm"
                                placeholder="Contoh : 335/AP.08/DIR/XI/{{ \Carbon\Carbon::parse(now())->format('Y') }}"
                                id="noSurat" name="noSurat" required>
                        </div>

                        <button type="submit" class="mt-4 btn btn-success btn-sm">
                            <i class="mdi mdi-printer-outline"></i> Cetak
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- IJIN PKL & ta --}}
    <div class="modal fade" id="ijinShowModal" tabindex="-1" aria-labelledby="ijinShowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ijinShowModalLabel">Verifikasi Data Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Nama</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="namaIjin"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">NIM</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="nimIjin"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Program Studi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="prodiIjin"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Tempat, tgl lahir</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="ttlIjin"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alamat</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="alamatIjin"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">No. Tlp</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="tlpIjin"></span>
                        </div>
                    </div>

                    <div class="mt-3 mb-3 fw-bold Label">Surat Ijin Memperoleh Data </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Nama Instansi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="namaInstansiIjin"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alamat Instansi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="alamatInstansiIjin"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Judul Laporan</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="judulLaporanIjin"></span></div>
                    </div>

                    <div class="mt-3 mb-3 fw-bold">Data yang Diminta</div>
                    <ul id="dimintaList"></ul>

                    <form action="{{ route('permohonan-surat-disetujui-update') }}" method="POST" target="_blank">
                        @method('PUT')
                        @csrf
                        <input type="hidden" id="ijinEdit" name="id">

                        <div class="mb-3">
                            <label for="noSurat" class="form-label fw-bold mt-2">Nomor Surat</label>
                            <input type="text" class="form-control form-control-sm"
                                placeholder="Contoh : 335/AP.08/DIR/XI/{{ \Carbon\Carbon::parse(now())->format('Y') }}"
                                id="noSurat" name="noSurat" required>
                        </div>

                        <button type="submit" class="mt-4 btn btn-success btn-sm">
                            <i class="mdi mdi-printer-outline"></i> Cetak
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ijinPKLShowModal" tabindex="-1" aria-labelledby="ijinShowPKLModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ijinShowPKLModalLabel">Verifikasi Data Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Nama</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="namaIjinPKL"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">NIM</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="nimIjinPKL"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Program Studi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="prodiIjinPKL"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Tempat, tgl lahir</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="ttlIjinPKL"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alamat</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="alamatIjinPKL"></span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">No. Tlp</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="tlpIjinPKL"></span>
                        </div>
                    </div>

                    <div class="mt-3 mb-3 fw-bold Label">Surat Ijin PKL </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Nama Instansi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="namaInstansiIjinPKL"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Alamat Instansi</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="alamatInstansiIjinPKL"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Pimpinan</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="pimpinanIjinPKL"></span></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 col-sm-4">Waktu</div>
                        <div class="d-none d-sm-block col-sm-1 text-end">:</div>
                        <div class="col-8 col-sm-6"><span class="d-inline d-sm-none">: </span><span
                                id="waktuijinPKL"></span></div>
                    </div>

                    <form action="{{ route('permohonan-surat-disetujui-update') }}" method="POST" target="_blank">
                        @method('PUT')
                        @csrf
                        <input type="hidden" id="ijinPKLIdEdit" name="id">

                        <div class="mb-3">
                            <label for="noSurat" class="form-label fw-bold mt-2">Nomor Surat</label>
                            <input type="text" class="form-control form-control-sm"
                                placeholder="Contoh : 335/AP.08/DIR/XI/{{ \Carbon\Carbon::parse(now())->format('Y') }}"
                                id="noSurat" name="noSurat" required>
                        </div>

                        <button type="submit" class="mt-4 btn btn-success btn-sm">
                            <i class="mdi mdi-printer-outline"></i> Cetak
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        // AKTIF KULIAH
        $(document).on('click', '.keteranganAktifEdit', function() {
            let id = $(this).data('id');
            let alamatOrangTua = $(this).data('alamatOrangTua');
            let pekerjaan = $(this).data('pekerjaan');
            let nip = $(this).data('nip');
            let pangkatAtauGolongan = $(this).data('pangkatAtauGolongan');
            let namaInstansi = $(this).data('namaInstansi');
            let alamatInstansi = $(this).data('alamatInstansi');
            let keperluan = $(this).data('keperluan');
            let masaStudi = $(this).data('masaStudi');
            let alasanCuti = $(this).data('alasanCuti');
            let namaOrangTua = $(this).data('namaOrangTua');
            let tahunAkademik = $(this).data('tahuAkademik');
            let nama = $(this).data('nama');
            let nim = $(this).data('nim');
            let prodi = $(this).data('prodi');
            let ttl = $(this).data('tempatTglLahir');
            let alamat = $(this).data('alamat');
            let telp = $(this).data('telp');



            $('#aktifKuliahIdEdit').val(id);
            $('#alamatOrangTuaAktif').text(alamatOrangTua);
            $('#namaOrangTuaAktif').text(namaOrangTua);
            $('#pekerjaanAktif').text(pekerjaan);
            $('#nipAktif').text(nip);
            $('#pangkatAtauGolonganAktif').text(pangkatAtauGolongan);
            $('#namaInstansiAktif').text(namaInstansi);
            $('#alamatInstansiAktif').text(alamatInstansi);
            $('#keperluanAktif').text(keperluan);
            $('#masaStudiAktif').text(masaStudi);
            $('#alasanCutiAktif').text(alasanCuti);
            $('#tahunAkademikAktif').text(tahunAkademik);
            $('#namaAktif').text(nama);
            $('#nimAktif').text(nim);
            $('#prodiAktif').text(prodi);
            $('#ttlAktif').text(ttl);
            $('#alamatAktif').text(alamat);
            $('#tlpAktif').text(telp);
        });

        $(document).on('click', '.cutiKuliahEdit', function() {
            let id = $(this).data('id');
            let alamatOrangTua = $(this).data('alamatOrangTua');
            let pekerjaan = $(this).data('pekerjaan');
            let nip = $(this).data('nip');
            let pangkatAtauGolongan = $(this).data('pangkatAtauGolongan');
            let namaInstansi = $(this).data('namaInstansi');
            let alamatInstansi = $(this).data('alamatInstansi');
            let keperluan = $(this).data('keperluan');
            let masaCuti = $(this).data('masaCuti');
            let alasanCuti = $(this).data('alasanCuti');
            let namaOrangTua = $(this).data('namaOrangTua');
            let tahunAkademik = $(this).data('tahuAkademik');
            let nama = $(this).data('nama');
            let nim = $(this).data('nim');
            let prodi = $(this).data('prodi');
            let ttl = $(this).data('tempatTglLahir');
            let alamat = $(this).data('alamat');
            let telp = $(this).data('telp');



            $('#cutiKuliahIdEdit').val(id);
            $('#masaCuti').text(masaCuti);
            $('#alasanCutiCuti').text(alasanCuti);
            $('#tahunAkademikCuti').text(tahunAkademik);
            $('#namaCuti').text(nama);
            $('#nimCuti').text(nim);
            $('#prodiCuti').text(prodi);
            $('#ttlCuti').text(ttl);
            $('#alamatCuti').text(alamat);
            $('#tlpCuti').text(telp);
        });

        // PERPANJANGAN
        $(document).on('click', '.perpanjanganStudiEdit', function() {
            let id = $(this).data('id');
            let alamatOrangTua = $(this).data('alamatOrangTua');
            let pekerjaan = $(this).data('pekerjaan');
            let nip = $(this).data('nip');
            let pangkatAtauGolongan = $(this).data('pangkatAtauGolongan');
            let namaInstansi = $(this).data('namaInstansi');
            let alamatInstansi = $(this).data('alamatInstansi');
            let keperluan = $(this).data('keperluan');
            let masaStudi = $(this).data('masaStudi');
            let alasanCuti = $(this).data('alasanCuti');
            let namaOrangTua = $(this).data('namaOrangTua');
            let tahunAkademik = $(this).data('tahuAkademik');
            let nama = $(this).data('nama');
            let nim = $(this).data('nim');
            let prodi = $(this).data('prodi');
            let ttl = $(this).data('tempatTglLahir');
            let alamat = $(this).data('alamat');
            let telp = $(this).data('telp');



            $('#perpanjanganStudiIdEdit').val(id);
            $('#alamatOrangTuaPerpanjangan').text(alamatOrangTua);
            $('#namaOrangTuaPerpanjangan').text(namaOrangTua);
            $('#pekerjaanPerpanjangan').text(pekerjaan);
            $('#nipPerpanjangan').text(nip);
            $('#pangkatAtauGolonganPerpanjangan').text(pangkatAtauGolongan);
            $('#namaInstansiPerpanjangan').text(namaInstansi);
            $('#alamatInstansiPerpanjangan').text(alamatInstansi);
            $('#keperluanPerpanjangan').text(keperluan);
            $('#masaStudiPerpanjangan').text(masaStudi);
            $('#alasanPerpanjanganPerpanjangan').text(alasanCuti);
            $('#tahunAkademikPerpanjangan').text(tahunAkademik);
            $('#namaPerpanjangan').text(nama);
            $('#nimPerpanjangan').text(nim);
            $('#prodiPerpanjangan').text(prodi);
            $('#ttlPerpanjangan').text(ttl);
            $('#alamatPerpanjangan').text(alamat);
            $('#tlpPerpanjangan').text(telp);
        });

        // PINDAH KELAS
        $(document).on('click', '.pindahKelasEdit', function() {
            let id = $(this).data('id');
            let alamatOrangTua = $(this).data('alamatOrangTua');
            let pekerjaan = $(this).data('pekerjaan');
            let nip = $(this).data('nip');
            let kelasAsal = $(this).data('kelasAsal');
            let kelasTujuan = $(this).data('kelasTujuan');
            let alamatInstansi = $(this).data('alamatInstansi');
            let keperluan = $(this).data('keperluan');
            let masaStudi = $(this).data('masaStudi');
            let alasanCuti = $(this).data('alasanCuti');
            let namaOrangTua = $(this).data('namaOrangTua');
            let tahunAkademik = $(this).data('tahuAkademik');
            let nama = $(this).data('nama');
            let nim = $(this).data('nim');
            let prodi = $(this).data('prodi');
            let ttl = $(this).data('tempatTglLahir');
            let alamat = $(this).data('alamat');
            let telp = $(this).data('telp');


            $('#pindahKelasIdEdit').val(id);
            $('#kelasAsal').text(kelasAsal);
            $('#kelasTujuan').text(kelasTujuan);
            $('#namaPindahKelas').text(nama);
            $('#nimPindahKelas').text(nim);
            $('#prodiPindahKelas').text(prodi);
            $('#ttlPindahKelas').text(ttl);
            $('#alamatPindahKelas').text(alamat);
            $('#tlpPindahKelas').text(telp);
        });

        $(document).on('click', '.pindahPTEdit', function() {
            let id = $(this).data('id');
            let alamatOrangTua = $(this).data('alamatOrangTua');
            let pekerjaan = $(this).data('pekerjaan');
            let nip = $(this).data('nip');
            let ptAsal = $(this).data('ptAsal');
            let ptTujuan = $(this).data('ptTujuan');
            let alamatInstansi = $(this).data('alamatInstansi');
            let keperluan = $(this).data('keperluan');
            let masaStudi = $(this).data('masaStudi');
            let alasanCuti = $(this).data('alasanCuti');
            let namaOrangTua = $(this).data('namaOrangTua');
            let tahunAkademik = $(this).data('tahuAkademik');
            let nama = $(this).data('nama');
            let nim = $(this).data('nim');
            let prodi = $(this).data('prodi');
            let ttl = $(this).data('tempatTglLahir');
            let alamat = $(this).data('alamat');
            let telp = $(this).data('telp');


            $('#pindahPTIdEdit').val(id);
            $('#ptAsal').text(ptAsal);
            $('#ptTujuan').text(ptTujuan);
            $('#namaPindahPT').text(nama);
            $('#nimPindahPT').text(nim);
            $('#prodiPindahPT').text(prodi);
            $('#ttlPindahPT').text(ttl);
            $('#alamatPindahPT').text(alamat);
            $('#tlpPindahPT').text(telp);
        });

        // MENGUNDURKAN DIRI
        $(document).on('click', '.mengundurkanDiriEdit', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let nim = $(this).data('nim');
            let prodi = $(this).data('prodi');
            let ttl = $(this).data('tempatTglLahir');
            let alamat = $(this).data('alamat');
            let telp = $(this).data('telp');

            $('#mengundurkanDiriIdEdit').val(id);
            $('#namaMengundurkanDiri').text(nama);
            $('#nimMengundurkanDiri').text(nim);
            $('#prodiMengundurkanDiri').text(prodi);
            $('#ttlMengundurkanDiri').text(ttl);
            $('#alamatMengundurkanDiri').text(alamat);
            $('#tlpMengundurkanDiri').text(telp);
        });

        // IJIN MEMPEROLEH DATA
        $(document).on('click', '.ijinEdit', function() {
            let id = $(this).data('id');
            let namaInstansi = $(this).data('namaInstansi');
            let alamatInstansi = $(this).data('alamatInstansi');
            let judulLaporan = $(this).data('judulLaporan');
            let nama = $(this).data('nama');
            let nim = $(this).data('nim');
            let prodi = $(this).data('prodi');
            let ttl = $(this).data('tempatTglLahir');
            let alamat = $(this).data('alamat');
            let telp = $(this).data('telp');
            let label = $(this).data('label')
            let diminta = $(this).data('diminta');



            $('#ijinEdit').val(id);
            $('#alamatInstansiIjin').text(alamatInstansi);
            $('#namaInstansiIjin').text(namaInstansi);
            $('#judulLaporanIjin').text(judulLaporan);
            $('#namaIjin').text(nama);
            $('#nimIjin').text(nim);
            $('#prodiIjin').text(prodi);
            $('#ttlIjin').text(ttl);
            $('#alamatIjin').text(alamat);
            $('#tlpIjin').text(telp);
            $('.Label').append(` ${label}`)
            $('#dimintaList').empty();

            if (diminta && Array.isArray(diminta)) {
                diminta.forEach(item => {
                    $('#dimintaList').append(`<li>${item}</li>`);
                });
            } else {
                $('#dimintaList').append(`<li>Tidak ada data diminta</li>`);
            }

            $('#ijinShowModal').on('hidden.bs.modal', function() {
                $('.Label').html('Surat Ijin Memperoleh Data');
                $('#dimintaList').empty();
            });

            $('#ijinShowModal').on('hidden.bs.modal', function() {
                $('.Label').html('Surat Ijin Memperoleh Data');
            });

        });

        $(document).on('click', '.ijinPKLEdit', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let nim = $(this).data('nim');
            let prodi = $(this).data('prodi');
            let ttl = $(this).data('tempatTglLahir');
            let alamat = $(this).data('alamat');
            let telp = $(this).data('telp');
            let namaInstansi = $(this).data('namaInstansi');
            let alamatInstansi = $(this).data('alamatInstansi');
            let pimpinan = $(this).data('pimpinan');
            let waktu = $(this).data('waktu');





            $('#ijinPKLIdEdit').val(id);
            $('#namaIjinPKL').text(nama);
            $('#nimIjinPKL').text(nim);
            $('#prodiIjinPKL').text(prodi);
            $('#ttlIjinPKL').text(ttl);
            $('#alamatIjinPKL').text(alamat);
            $('#tlpIjinPKL').text(telp);
            $('#namaInstansiIjinPKL').text(namaInstansi);
            $('#alamatInstansiIjinPKL').text(alamatInstansi);
            $('#pimpinanIjinPKL').text(pimpinan);
            $('#waktuijinPKL').text(waktu);
        });
    </script>
    @if (session('success'))
        <script>
            Swal.fire({
                title: 'Sukses!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
@endsection
