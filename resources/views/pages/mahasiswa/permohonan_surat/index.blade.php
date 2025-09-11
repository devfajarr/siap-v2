@extends('layouts.main')

@section('container')
    <style>
        .status-circle {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .bg-success {
            background-color: green;
        }

        .bg-danger {
            background-color: red;
        }

        .progress-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            width: 80%;
            margin: 0 auto;
            padding-top: 20px;
        }

        .progress-line {
            position: absolute;
            top: 30px;
            left: 12.5%;
            width: 75%;
            height: 4px;
            background-color: #ddd;
            z-index: 0;
        }

        .progress-step {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 25%;
            z-index: 1;
        }

        .progress-circle {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background-color: #fff;
            border: 3px solid #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            font-weight: bold;
            color: #ddd;
        }

        .progress-step.active .progress-circle {
            border-color: #007bff;
            color: #007bff;
        }

        .progress-step.completed .progress-circle {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .progress-step.completed .progress-circle::after {
            content: "\2713";
            /* Unicode untuk simbol centang */
            font-size: 16px;
            /* Sesuaikan ukuran */
            font-weight: bold;
            color: white;
            /* Warna centang */
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .progress-label {
            margin-top: 8px;
            font-size: 14px;
            color: #333;
        }

        /* Kelas progress-fill untuk digunakan secara dinamis */
        .progress-fill {
            position: absolute;
            top: 30px;
            left: 12.5%;
            height: 4px;
            background-color: #007bff;
            z-index: 1;
        }

        /* Kelas-kelas lebar yang bisa diaplikasikan secara dinamis */
        .fill-25 {
            width: 0%;
        }


        .fill-50 {
            width: 25%;
        }

        .fill-75 {
            width: 50%;
        }

        .fill-100 {
            width: 75%;
        }

        h2 {
            margin-top: 30px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
    </style>
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="breadcrumb">
                <a href="/presensi/dashboard" class="breadcrumb-item">
                    <span class="mdi mdi-home"></span> Dashboard
                </a>
                <span class="breadcrumb-item">Permohonan Surat</span>
            </div>
            <div class="row">
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Permohonan Surat</h4>
                        </div>
                        <div class="card-body">
                            <div class="container">
                                <div class="row">
                                    <div class="mb-3 col-md-3">
                                        <label for="jenisSurat" class="form-label">Pilih Jenis Surat:</label>
                                        <select class="form-select" id="jenisSurat" name="jenisSurat" onchange="showForm()">
                                            <option value="">-- Pilih Jenis Surat --</option>
                                            <option value="aktifKuliah"
                                                {{ old('jenisSurat') == 'aktifKuliah' ? 'selected' : '' }}>Surat Keterangan
                                                Aktif Kuliah</option>
                                            <option value="cutiKuliah"
                                                {{ old('jenisSurat') == 'cutiKuliah' ? 'selected' : '' }}>Cuti Kuliah
                                            </option>
                                            <option value="pindahKelas"
                                                {{ old('jenisSurat') == 'pindahKelas' ? 'selected' : '' }}>Pindah Kelas
                                            </option>
                                            <option value="pindahPT"
                                                {{ old('jenisSurat') == 'pindahPT' ? 'selected' : '' }}>Pindah PT</option>
                                            <option value="mengundurkanDiri"
                                                {{ old('jenisSurat') == 'mengundurkanDiri' ? 'selected' : '' }}>Mengundurkan
                                                Diri</option>
                                            <option value="ijinPKL" {{ old('jenisSurat') == 'ijinPKL' ? 'selected' : '' }}>
                                                Ijin
                                                PKL</option>
                                            <option value="memperolehDataPKL"
                                                {{ old('jenisSurat') == 'memperolehDataPKL' ? 'selected' : '' }}>Ijin
                                                Memperoleh Data PKL</option>
                                            <option value="memperolehDataTA"
                                                {{ old('jenisSurat') == 'memperolehDataTA' ? 'selected' : '' }}>Ijin
                                                Memperoleh Data TA</option>
                                        </select>
                                        <div id="jenisSuratError" class="invalid-feedback"></div>
                                    </div>
                                </div>

                                {{-- KETERANGAN AKTIF KULIAH --}}
                                <div id="ketAktifKuliah" class="hidden">
                                    <form action="{{ route('create-permohonan-surat') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="jenis_permohonan" value="Keterangan Aktif Kuliah">
                                        <input type="hidden" name="jenisSurat" value="aktifKuliah">
                                        <div class="row">
                                            <div class="mb-3 col-md-4">
                                                <label for="namaOrangTua" class="form-label">Nama Orang Tua</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('namaOrangTua') is-invalid @enderror"
                                                    id="namaOrangTua" name="namaOrangTua" placeholder="Nama Orang Tua"
                                                    value="{{ old('namaOrangTua') }}">
                                                @error('namaOrangTua')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-8">
                                                <label for="alamatOrangTua" class="form-label">Alamat Orang Tua</label>
                                                <textarea class="form-control form-control-sm @error('alamatOrangTua') is-invalid @enderror" id="alamatOrangTua"
                                                    name="alamatOrangTua" placeholder="Alamat Lengkap">{{ old('alamatOrangTua') }}</textarea>
                                                @error('alamatOrangTua')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="mb-3 col-md-4">
                                                <label for="pekerjaan" class="form-label">Pekerjaan</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('pekerjaan') is-invalid @enderror"
                                                    id="pekerjaan" name="pekerjaan" placeholder="Pekerjaan"
                                                    value="{{ old('pekerjaan') }}">
                                                @error('pekerjaan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label for="nip" class="form-label">NIP</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('nip') is-invalid @enderror"
                                                    id="nip" name="nip" placeholder="NIP"
                                                    value="{{ old('nip') }}">
                                                @error('nip')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label for="pangkatAtauGolongan" class="form-label">Pangkat/Golongan</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('pangkatAtauGolongan') is-invalid @enderror"
                                                    id="pangkatAtauGolongan" name="pangkatAtauGolongan"
                                                    placeholder="Pangkat/Golongan"
                                                    value="{{ old('pangkatAtauGolongan') }}">
                                                @error('pangkatAtauGolongan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label for="namaInstansi" class="form-label">Nama Instansi</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('namaInstansi') is-invalid @enderror"
                                                    id="namaInstansi" name="namaInstansi" placeholder="Nama Instansi"
                                                    value="{{ old('namaInstansi') }}">
                                                @error('namaInstansi')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label for="alamatInstansi" class="form-label">Alamat Instansi</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('alamatInstansi') is-invalid @enderror"
                                                    id="alamatInstansi" name="alamatInstansi"
                                                    placeholder="Contoh: Jl. Diponegoro No. 12 Kutoarjo, Kutoarjo, Purworejo, Jawa Tengah"
                                                    value="{{ old('alamatInstansi') }}">
                                                @error('alamatInstansi')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label for="keperluan" class="form-label">Keperluan</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('keperluan') is-invalid @enderror"
                                                    id="keperluan" name="keperluan" placeholder="Keperluan"
                                                    value="{{ old('keperluan') }}">
                                                @error('keperluan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label for="tahunAkademik" class="form-label">Tahun Akademik</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('tahunAkademik') is-invalid @enderror"
                                                    id="tahunAkademik" name="tahunAkademik"
                                                    value="{{ old('tahunAkademik', $tahun_awal . '/GASAL /' . $tahun_akhir . '/GENAP') }}"
                                                    style="pointer-events: none; background-color: #e9ecef;"
                                                    tabindex="-1">
                                                @error('tahunAkademik')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm" type="submit">Buat Permohonan</button>
                                    </form>
                                </div>

                                {{-- CUTI KULIAH --}}
                                <div id="cutiKuliah" class="hidden">
                                    <form action="{{ route('create-permohonan-surat') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="jenis_permohonan" value="Cuti Kuliah">
                                        <input type="hidden" name="jenisSurat" value="cutiKuliah">
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label for="masaCuti" class="form-label">Semester</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('masaCuti') is-invalid @enderror"
                                                    id="masaCuti" name="masaCuti" placeholder="Alasan Cuti"
                                                    value="{{ $masaCuti }}"
                                                    style="pointer-events: none; background-color: #e9ecef;"
                                                    tabindex="-1">
                                                @error('masaCuti')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label for="tahunAkademik" class="form-label">Tahun Akademik</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('tahunAkademik') is-invalid @enderror"
                                                    id="tahunAkademik" name="tahunAkademik"
                                                    value="{{ old('tahunAkademik', $tahun_awal . '/GASAL /' . $tahun_akhir . '/GENAP') }}"
                                                    style="pointer-events: none; background-color: #e9ecef;"
                                                    tabindex="-1">
                                                @error('tahunAkademik')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label for="alasanCuti" class="form-label">Alasan Cuti</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('alasanCuti') is-invalid @enderror"
                                                    id="alasanCuti" name="alasanCuti" placeholder="Alasan Cuti"
                                                    value="{{ old('alasanCuti') }}">
                                                @error('alasanCuti')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm" type="submit">Buat Permohonan</button>
                                    </form>
                                </div>

                                {{-- PINDAH KELAS --}}
                                <div id="pindahKelas" class="hidden">
                                    <form action="{{ route('create-permohonan-surat') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="jenis_permohonan" value="Pindah Kelas">
                                        <input type="hidden" name="jenisSurat" value="pindahKelas">
                                        <div class="row">
                                            <div class="mb-3 col-md-4">
                                                <label for="kelasAsal" class="form-label">Kelas Asal</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('kelasAsal') is-invalid @enderror"
                                                    id="kelasAsal" name="kelasAsal" placeholder="Kelas Asal"
                                                    value="{{ $kelasAsal }}"
                                                    style="pointer-events: none; background-color: #e9ecef;"
                                                    tabindex="-1">
                                                @error('kelasAsal')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label for="kelasTujuan" class="form-label">Kelas Tujuan</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('kelasTujuan') is-invalid @enderror"
                                                    id="kelasTujuan" name="kelasTujuan" placeholder="Kelas Tujuan"
                                                    value="{{ $kelasTujuan }}"
                                                    style="pointer-events: none; background-color: #e9ecef;"
                                                    tabindex="-1">
                                                @error('kelasTujuan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm" type="submit">Buat Permohonan</button>
                                    </form>

                                </div>

                                {{-- PINDAH PT --}}
                                <div id="pindahPT" class="hidden">
                                    <form action="{{ route('create-permohonan-surat') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="jenis_permohonan" value="Pindah PT">
                                        <input type="hidden" name="jenisSurat" value="pindahPT">
                                        <div class="row">
                                            <div class="mb-3 col-md-4">
                                                <label for="kelasAsal" class="form-label">PT Asal</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('ptAsal') is-invalid @enderror"
                                                    id="ptAsal" name="ptAsal" placeholder="PT Asal"
                                                    value="Politeknik Sawunggalih Aji"
                                                    style="pointer-events: none; background-color: #e9ecef;"
                                                    tabindex="-1">
                                                @error('ptAsal')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label for="ptTujuan" class="form-label">PT Tujuan</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('ptTujuan') is-invalid @enderror"
                                                    id="ptTujuan" name="ptTujuan" placeholder="PT Tujuan"
                                                    value="{{ old('ptTujuan') }}">
                                                @error('ptTujuan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label for="statusAkreditasi" class="form-label">Status Akreditasi</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('statusAkreditasi') is-invalid @enderror"
                                                    id="statusAkreditasi" name="statusAkreditasi"
                                                    placeholder="Status Akreditasi"
                                                    value="{{ old('statusAkreditasi') }}">
                                                @error('statusAkreditasi')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm" type="submit">Buat Permohonan</button>
                                    </form>

                                </div>

                                {{-- MENGUNDURKA DIRI --}}
                                <div id="mengundurkanDiri" class="hidden">
                                    <form action="{{ route('create-permohonan-surat') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="jenis_permohonan" value="Mengundurkan Diri">
                                        <input type="hidden" name="jenisSurat" value="mengundurkanDiri">
                                        <p class="text-danger">⚠️ Peringatan:</p>
                                        <p class="text-danger">
                                            Anda sedang memuat Permohonan Surat Pengunduran Diri dari Perguruan Tinggi.
                                            Pastikan Anda telah mempertimbangkan keputusan ini dengan matang, karena
                                            pengunduran diri bersifat permanen dan dapat memengaruhi status akademik serta
                                            hak mahasiswa. Jika Anda yakin untuk melanjutkan, silakan lanjutkan proses
                                            permohonan.</p>
                                        <button class="btn btn-primary btn-sm" type="submit">Buat Permohonan</button>
                                    </form>
                                </div>

                                {{-- IJIN PKL --}}
                                <div id="ijinPKL" class="hidden">
                                    <form action="{{ route('create-permohonan-surat') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="jenis_permohonan" value="Ijin PKL">
                                        <input type="hidden" name="jenisSurat" value="ijinPKL">
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label for="namaInstansi" class="form-label">Nama Instansi</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('namaInstansi') is-invalid @enderror"
                                                    id="namaInstansi" name="namaInstansi" placeholder="Nama Instansi"
                                                    value="{{ old('namaInstansi') }}">
                                                @error('namaInstansi')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label for="alamatInstansi" class="form-label">Pimpinan</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('pimpinan') is-invalid @enderror"
                                                    id="pimpinan" name="pimpinan" placeholder="Nama Pimpinan"
                                                    value="{{ old('pimpinan') }}">
                                                @error('pimpinan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label for="alamatInstansi" class="form-label">Alamat Instansi</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('alamatInstansi') is-invalid @enderror"
                                                    id="alamatInstansi" name="alamatInstansi"
                                                    placeholder="Contoh: Jl. Diponegoro No. 12 Kutoarjo, Kutoarjo, Purworejo, Jawa Tengah"
                                                    value="{{ old('alamatInstansi') }}">
                                                @error('alamatInstansi')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label for="waktuMulai" class="form-label">Tanggal Mulai</label>
                                                <input type="date"
                                                    class="form-control form-control-sm @error('waktuMulai') is-invalid @enderror"
                                                    id="waktuMulai" name="waktuMulai" value="{{ old('waktuMulai') }}">
                                                @error('waktuMulai')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label for="waktuSelesai" class="form-label">Tanggal Selesai</label>
                                                <input type="date"
                                                    class="form-control form-control-sm @error('waktuSelesai') is-invalid @enderror"
                                                    id="waktuSelesai" name="waktuSelesai"
                                                    value="{{ old('waktuSelesai') }}">
                                                @error('waktuSelesai')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm" type="submit">Buat Permohonan</button>
                                    </form>
                                </div>

                                {{-- IJIN MEMPEROLEH DATA TA --}}
                                <div id="memperolehDataTA" class="hidden">
                                    <form action="{{ route('create-permohonan-surat') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="jenis_permohonan" value="Ijin Memperoleh Data TA">
                                        <input type="hidden" name="jenisSurat" value="memperolehDataTA">
                                        <div class="row">
                                            <div class="mb-3 col-md-4">
                                                <label for="namaInstansi" class="form-label">Nama Instansi</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('namaInstansi') is-invalid @enderror"
                                                    id="namaInstansi" name="namaInstansi" placeholder="Nama Instansi"
                                                    value="{{ old('namaInstansi') }}">
                                                @error('namaInstansi')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label for="alamatInstansi" class="form-label">Alamat Instansi</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('alamatInstansi') is-invalid @enderror"
                                                    id="alamatInstansi" name="alamatInstansi"
                                                    placeholder="Contoh: Jl. Diponegoro No. 12 Kutoarjo, Kutoarjo, Purworejo, Jawa Tengah"
                                                    value="{{ old('alamatInstansi') }}">
                                                @error('alamatInstansi')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label for="judulLaporan" class="form-label">Judul Laporan</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('judulLaporan') is-invalid @enderror"
                                                    id="judulLaporan" name="judulLaporan" placeholder="Judul Laporan"
                                                    value="{{ old('judulLaporan') }}">
                                                @error('judulLaporan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label for="dataDimintaTA" class="form-label">Data yang Diminta
                                                    (TA)</label>
                                                <div id="dataDimintaContainerTA">
                                                    @php
                                                        $oldDataDiminta = old('dataDimintaTA', []);
                                                    @endphp

                                                    @if (count($oldDataDiminta) > 0)
                                                        @foreach ($oldDataDiminta as $key => $data)
                                                            <div class="input-group mb-2">
                                                                <input type="text" name="dataDimintaTA[]"
                                                                    class="form-control form-control-sm @error("dataDimintaTA.$key") is-invalid @enderror"
                                                                    placeholder="Masukkan data yang diminta"
                                                                    value="{{ $data }}">
                                                                @if ($key === 0)
                                                                    <button type="button"
                                                                        class="btn btn-primary btn-sm addDataTA"><i
                                                                            class="mdi mdi-plus"></i></button>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-secondary btn-sm removeDataTA"><i
                                                                            class="mdi mdi-minus"></i></button>
                                                                @endif
                                                            </div>
                                                            @error("dataDimintaTA.$key")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        @endforeach
                                                    @else
                                                        <div class="input-group mb-2">
                                                            <input type="text" name="dataDimintaTA[]"
                                                                class="form-control form-control-sm @error('dataDimintaTA') is-invalid @enderror"
                                                                placeholder="Masukkan data yang diminta">
                                                            <button type="button"
                                                                class="btn btn-primary btn-sm addDataTA"><i
                                                                    class="mdi mdi-plus"></i></button>
                                                        </div>
                                                    @endif
                                                </div>
                                                @error('dataDimintaTA')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm" type="submit">Buat Permohonan</button>
                                    </form>
                                </div>

                                <div id="memperolehDataPKL" class="hidden">
                                    <form action="{{ route('create-permohonan-surat') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="jenis_permohonan" value="Ijin Memperoleh Data PKL">
                                        <input type="hidden" name="jenisSurat" value="memperolehDataPKL">
                                        <div class="row">
                                            <div class="mb-3 col-md-4">
                                                <label for="namaInstansi" class="form-label">Nama Instansi</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('namaInstansi') is-invalid @enderror"
                                                    id="namaInstansi" name="namaInstansi" placeholder="Nama Instansi"
                                                    value="{{ old('namaInstansi') }}">
                                                @error('namaInstansi')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label for="alamatInstansi" class="form-label">Alamat Instansi</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('alamatInstansi') is-invalid @enderror"
                                                    id="alamatInstansi" name="alamatInstansi"
                                                    placeholder="Contoh: Jl. Diponegoro No. 12 Kutoarjo, Kutoarjo, Purworejo, Jawa Tengah"
                                                    value="{{ old('alamatInstansi') }}">
                                                @error('alamatInstansi')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label for="judulLaporan" class="form-label">Judul Laporan</label>
                                                <input type="text"
                                                    class="form-control form-control-sm @error('judulLaporan') is-invalid @enderror"
                                                    id="judulLaporan" name="judulLaporan" placeholder="Judul Laporan"
                                                    value="{{ old('judulLaporan') }}">
                                                @error('judulLaporan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label for="dataDimintaPKL" class="form-label">Data yang Diminta
                                                    (PKL)</label>
                                                <div id="dataDimintaContainerPKL">
                                                    @php
                                                        $oldDataDimintaPKL = old('dataDimintaPKL', []);
                                                    @endphp

                                                    @if (count($oldDataDimintaPKL) > 0)
                                                        @foreach ($oldDataDimintaPKL as $key => $data)
                                                            <div class="input-group mb-2">
                                                                <input type="text" name="dataDimintaPKL[]"
                                                                    class="form-control form-control-sm @error("dataDimintaPKL.$key") is-invalid @enderror"
                                                                    placeholder="Masukkan data yang diminta"
                                                                    value="{{ $data }}">
                                                                @if ($key === 0)
                                                                    <button type="button"
                                                                        class="btn btn-primary btn-sm addDataPKL"><i
                                                                            class="mdi mdi-plus"></i></button>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-secondary btn-sm removeDataPKL"><i
                                                                            class="mdi mdi-minus"></i></button>
                                                                @endif
                                                            </div>
                                                            @error("dataDimintaPKL.$key")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        @endforeach
                                                    @else
                                                        <div class="input-group mb-2">
                                                            <input type="text" name="dataDimintaPKL[]"
                                                                class="form-control form-control-sm @error('dataDimintaPKL') is-invalid @enderror"
                                                                placeholder="Masukkan data yang diminta">
                                                            <button type="button"
                                                                class="btn btn-primary btn-sm addDataPKL"><i
                                                                    class="mdi mdi-plus"></i></button>
                                                        </div>
                                                    @endif
                                                </div>
                                                @error('dataDimintaPKL')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm" type="submit">Buat Permohonan</button>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Data Permohonan</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Jenis Permohonan</th>
                                            <th>Tanggal</th>
                                            <th style="text-align: center">Status</th>
                                            <th>Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($permohonans as $permohonan)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $permohonan->jenis_permohonan }}</td>
                                                <td>{{ \Carbon\Carbon::parse($permohonan->created_at)->locale('id')->translatedFormat('d F Y') }}
                                                </td>
                                                <td style="width: 60%">
                                                    <div class="progress-container">
                                                        <div class="progress-line"></div>
                                                        @if ($permohonan->setuju_kaprodi == 0)
                                                            <div class="progress-fill fill-50"></div>
                                                        @elseif($permohonan->setuju_kaprodi == 1 && $permohonan->status == 0)
                                                            <div class="progress-fill fill-75"></div>
                                                        @elseif($permohonan->setuju_kaprodi == 1 && $permohonan->status == 1)
                                                            <div class="progress-fill fill-100"></div>
                                                        @endif

                                                        <div class="progress-step completed">
                                                            <div class="progress-circle"></div>
                                                            <div class="progress-label">Draf</div>
                                                        </div>

                                                        <div
                                                            class="progress-step 
                                                            @if ($permohonan->setuju_kaprodi == 0) active 
                                                            @elseif($permohonan->setuju_kaprodi == 1) completed @endif">
                                                            <div class="progress-circle"></div>
                                                            <div class="progress-label">Kaprodi</div>
                                                        </div>

                                                        <div
                                                            class="progress-step 
                                                            @if ($permohonan->setuju_kaprodi == 1 && $permohonan->status == 0) active 
                                                            @elseif($permohonan->setuju_kaprodi == 1 && $permohonan->status == 1) completed @endif">
                                                            <div class="progress-circle"></div>
                                                            <div class="progress-label">Akademik</div>
                                                        </div>

                                                        <div
                                                            class="progress-step 
                                                            @if ($permohonan->setuju_kaprodi == 1 && $permohonan->status == 1) completed @endif">
                                                            <div class="progress-circle"></div>
                                                            <div class="progress-label">Selesai</div>
                                                        </div>

                                                    </div>
                                                </td>

                                                <td>
                                                    @if ($permohonan->jenis_permohonan != 'Mengundurkan Diri')
                                                        <button data-bs-toggle="modal"
                                                            @if ($permohonan->jenis_permohonan == 'Keterangan Aktif Kuliah') data-bs-target="#aktifKuliahShowModal" class="keteranganAktifEdit btn btn-sm btn-warning"
                                                        data-id="{{ $permohonan->id }}"
                                                        data-nama-orang-tua="{{ $permohonan->nama_orang_tua }}"
                                                        data-alamat-orang-tua="{{ $permohonan->alamat_orang_tua }}"
                                                        data-pekerjaan="{{ $permohonan->pekerjaan }}"
                                                        data-nip="{{ $permohonan->nip }}"
                                                        data-pangkat-atau-golongan="{{ $permohonan->pangkat_golongan }}"
                                                        data-nama-instansi="{{ $permohonan->nama_instansi }}"
                                                        data-alamat-instansi="{{ $permohonan->alamat_instansi }}"
                                                        data-keperluan="{{ $permohonan->keperluan }}"

                                                        @elseif ($permohonan->jenis_permohonan == 'Cuti Kuliah')
                                                        data-bs-target="#cutiKuliahShowModal" class="cutiKuliahEdit btn btn-sm btn-warning"
                                                        data-id="{{ $permohonan->id }}"
                                                        data-masa-cuti ="{{ $permohonan->masa_cuti }}"
                                                        data-alasan-cuti="{{ $permohonan->alasan_cuti }}"

                                                        @elseif($permohonan->jenis_permohonan == 'Pindah Kelas')
                                                        data-bs-target = '#pindahKelasShowModal' class="pindahKelasEdit btn btn-sm btn-warning"
                                                        data-id ="{{ $permohonan->id }}"
                                                        data-kelas-asal="{{ $permohonan->kelas_asal }}"
                                                        data-kelas-tujuan="{{ $permohonan->kelas_tujuan }}"

                                                        @elseif($permohonan->jenis_permohonan == 'Pindah PT')
                                                        data-bs-target = '#pindahPTShowModal' class="pindahPTEdit btn btn-sm btn-warning"
                                                        data-id ="{{ $permohonan->id }}"
                                                        data-pt-asal="{{ $permohonan->pt_asal }}"
                                                        data-pt-tujuan="{{ $permohonan->pt_tujuan }}"
                                                        data-status-akreditasi="{{ $permohonan->status_akreditasi }}"

                                                        @elseif($permohonan->jenis_permohonan == 'Ijin PKL')
                                                        data-bs-target = '#ijinPKLShowModal' class="ijinPKLEdit btn btn-sm btn-warning"
                                                        data-id ="{{ $permohonan->id }}"
                                                        data-nama-instansi="{{ $permohonan->nama_instansi }}"
                                                        data-alamat-instansi="{{ $permohonan->alamat_instansi }}"
                                                        data-pimpinan="{{ $permohonan->pimpinan }}"
                                                        data-tanggal-mulai="{{ $permohonan->tanggal_mulai }}"
                                                        data-tanggal-selesai="{{ $permohonan->tanggal_selesai }}"

                                                        @elseif($permohonan->jenis_permohonan == 'Ijin Memperoleh Data TA')
                                                        data-bs-target = '#memperolehDataTAShowModal' class="editDataTA btn btn-sm btn-warning"
                                                        data-id ={{ $permohonan->id }}
                                                        data-nama-instansi="{{ $permohonan->nama_instansi }}"
                                                        data-alamat-instansi="{{ $permohonan->alamat_instansi }}"
                                                        data-judul-laporan="{{ $permohonan->judul_laporan }}"
                                                        data-diminta='@json($permohonan->data_diminta)'
                                                        
                                                        @elseif($permohonan->jenis_permohonan == 'Ijin Memperoleh Data PKL')
                                                        data-bs-target = '#memperolehDataPKLShowModal' class="editDataPKL btn btn-sm btn-warning"
                                                        data-id ={{ $permohonan->id }}
                                                        data-nama-instansi="{{ $permohonan->nama_instansi }}"
                                                        data-alamat-instansi="{{ $permohonan->alamat_instansi }}"
                                                        data-judul-laporan="{{ $permohonan->judul_laporan }}"
                                                        data-diminta='@json($permohonan->data_diminta)' @endif>
                                                            <i class="mdi mdi-eye"></i> Lihat
                                                        </button>
                                                    @endif
                                                    <button class="btn btn-danger btn-sm delete-btn"
                                                        data-id="{{ $permohonan->id }}">
                                                        <span class="mdi mdi-delete"></span> Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" colspan="5">Belum ada permohonan</td>
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
                    <h5 class="modal-title" id="aktifKuliahShowModalLabel">Edit Formulir Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="aktifKuliahIdEdit" name="id">
                        <div class="row">
                            <div class="mb-3 col-md-4">
                                <label for="namaOrangTua" class="form-label">Nama Orang Tua</label>
                                <input type="text" class="form-control form-control-sm" id="namaOrangTuaAktif"
                                    name="namaOrangTua" placeholder="Nama Orang Tua" value="{{ old('namaOrangTua') }}">

                                <div class="invalid-feedback namaOrangTuaAktifError"></div>
                            </div>
                            <div class="mb-3 col-md-8">
                                <label for="alamatOrangTua" class="form-label">Alamat Orang Tua</label>
                                <textarea class="form-control form-control-sm" id="alamatOrangTuaAktif" name="alamatOrangTua"
                                    placeholder="Alamat Lengkap">{{ old('alamatOrangTua') }}</textarea>
                                <div class="invalid-feedback alamatOrangTuaAktifError"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-4">
                                <label for="pekerjaan" class="form-label">Pekerjaan</label>
                                <input type="text" class="form-control form-control-sm" id="pekerjaanAktif"
                                    name="pekerjaan" placeholder="Pekerjaan" value="{{ old('pekerjaan') }}">
                                <div class="invalid-feedback pekerjaanAktifError"></div>
                            </div>
                            <div class="mb-3 col-md-4">
                                <label for="nip" class="form-label">NIP</label>
                                <input type="text" class="form-control form-control-sm" id="nipAktif" name="nip"
                                    placeholder="NIP" value="{{ old('nip') }}">
                                <div class="invalid-feedback nipAktifError"></div>
                            </div>
                            <div class="mb-3 col-md-4">
                                <label for="pangkatAtauGolongan" class="form-label">Pangkat/Golongan</label>
                                <input type="text" class="form-control form-control-sm" id="pangkatAtauGolonganAktif"
                                    name="pangkatAtauGolongan" placeholder="Pangkat/Golongan"
                                    value="{{ old('pangkatAtauGolongan') }}">
                                <div class="invalid-feedback pangkatAtauGolonganAktifError"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="namaInstansi" class="form-label">Nama Instansi</label>
                                <input type="text" class="form-control form-control-sm" id="namaInstansiAktif"
                                    name="namaInstansi" placeholder="Nama Instansi" value="{{ old('namaInstansi') }}">
                                <div class="invalid-feedback namaInstansiAktifError"></div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="alamatInstansi" class="form-label">Alamat Instansi</label>
                                <input type="text" class="form-control form-control-sm" id="alamatInstansiAktif"
                                    name="alamatInstansi"
                                    placeholder="Contoh: Jl. Diponegoro No. 12 Kutoarjo, Kutoarjo, Purworejo, Jawa Tengah"
                                    value="{{ old('alamatInstansi') }}">
                                <div class="invalid-feedback alamatInstansiAktifError"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="keperluan" class="form-label">Keperluan</label>
                                <input type="text" class="form-control form-control-sm" id="keperluanAktif"
                                    name="keperluan" placeholder="Keperluan" value="{{ old('keperluan') }}">
                                <div class="invalid-feedback keperluanAktifError"></div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="tahunAkademik" class="form-label">Tahun Akademik</label>
                                <input type="text" class="form-control form-control-sm" id="tahunAkademikAktif"
                                    name="tahunAkademik"
                                    value="{{ old('tahunAkademik', $tahun_awal . '/GASAL /' . $tahun_akhir . '/GENAP') }}"
                                    style="pointer-events: none; background-color: #e9ecef;" tabindex="-1">
                                <div class="invalid-feedback tahunAkademikAktifError"></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" id="editButton"><span
                                class="mdi mdi-content-save"></span> Simpan</button>
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
                    <h5 class="modal-title" id="cutiKuliahShowModalLabel">Edit Formulir Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFormCuti">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="cutiKuliahIdEdit" name="id">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="masaCutiEdit" class="form-label">Semester</label>
                                <input type="text" class="form-control form-control-sm" id="masaCutiEdit"
                                    name="masaCutiEdit" placeholder="masaCuti" value="{{ old('masaCuti') }}"
                                    style="pointer-events: none; background-color: #e9ecef;" tabindex="-1">
                                <div class="invalid-feedback masaCutiEditError"></div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="tahunAkademik" class="form-label">Tahun Akademik</label>
                                <input type="text" class="form-control form-control-sm" id="tahunAkademikCuti"
                                    name="tahunAkademik"
                                    value="{{ old('tahunAkademik', $tahun_awal . '/GASAL /' . $tahun_akhir . '/GENAP') }}"
                                    style="pointer-events: none; background-color: #e9ecef;" tabindex="-1">
                                <div class="invalid-feedback tahunAkademikCutiError"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-12">
                                <label for="alasanCuti" class="form-label">Alasan Cuti</label>
                                <input type="text" class="form-control form-control-sm" id="alasanCutiCuti"
                                    name="alasanCuti" placeholder="Alasan Cuti" value="{{ old('alasanCuti') }}">
                                <div class="invalid-feedback alasanCutiCutiError"></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" id="editButton"><span
                                class="mdi mdi-content-save"></span> Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- PINDAH KELAS --}}
    <div class="modal fade" id="pindahKelasShowModal" tabindex="-1" aria-labelledby="pindahKelasShowModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pindahKelasShowModalLabel">Formulir Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFormPindahKelas">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="pindahKelasId" name="id">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="kelasAsal" class="form-label">Kelas Asal</label>
                                <input type="text" class="form-control form-control-sm" id="kelasAsalEdit"
                                    name="kelasAsal" placeholder="Kelas Asal" value="{{ $kelasAsal }}"
                                    style="pointer-events: none; background-color: #e9ecef;" tabindex="-1">
                                <div class="invalid-feedback kelasAsalError"></div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="kelasTujuan" class="form-label">Kelas Tujuan</label>
                                <input type="text" class="form-control form-control-sm" id="kelasTujuanEdit"
                                    name="kelasTujuan" placeholder="Kelas Tujuan" value="{{ $kelasTujuan }}"
                                    style="pointer-events: none; background-color: #e9ecef;" tabindex="-1">
                                <div class="invalid-feedback kelasTujuanError"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- PINDAH PT --}}
    <div class="modal fade" id="pindahPTShowModal" tabindex="-1" aria-labelledby="pindahPTShowModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pindahPTShowModalLabel">Edit Formulir Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFormPindahPT">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="pindahPTId" name="id">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="ptAsal" class="form-label">PT Asal</label>
                                <input type="text" class="form-control form-control-sm" id="ptAsalEdit"
                                    name="ptAsal" placeholder="PT Asal" value="{{ old('ptAsal') }}"
                                    style="pointer-events: none; background-color: #e9ecef;" tabindex="-1">
                                <div class="invalid-feedback ptAsalError"></div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="ptTujuan" class="form-label">PT Tujuan</label>
                                <input type="text" class="form-control form-control-sm" id="ptTujuanEdit"
                                    name="ptTujuan" placeholder="PT Tujuan" value="{{ old('ptTujuan') }}">
                                <div class="invalid-feedback ptTujuanError"></div>
                            </div>
                            <div class="mb-3 col-md-12">
                                <label for="statusAkreditasiEdit" class="form-label">Status Akreditasi</label>
                                <input type="text" class="form-control form-control-sm" id="statusAkreditasiEdit"
                                    name="statusAkreditasi" placeholder="PT Tujuan"
                                    value="{{ old('statusAkreditasi') }}">
                                <div class="invalid-feedback statusAkreditasiEditError"></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" id="editButton"><span
                                class="mdi mdi-content-save"></span> Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- IJIN PKL --}}
    <div class="modal fade" id="ijinPKLShowModal" tabindex="-1" aria-labelledby="ijinPKLShowModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ijinPKLShowModalLabel">Edit Formulir Permohonan Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFormijinPKL">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="ijinPKLId" name="id">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="namaInstansiPKL" class="form-label">Nama Instansi</label>
                                <input type="text" class="form-control form-control-sm" id="namaInstansiPKL"
                                    name="namaInstansi" placeholder="PT Asal" value="{{ old('namaInstansi') }}">
                                <div class="invalid-feedback namaInstansiPKLError"></div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="pimpinanPKL" class="form-label">Pimpinan</label>
                                <input type="text" class="form-control form-control-sm" id="pimpinanPKL"
                                    name="pimpinan" placeholder="Nama Pimpinan" value="{{ old('pimpinan') }}">
                                <div class="invalid-feedback pimpinanPKLError"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-12">
                                <label for="alamatInstansiPKL" class="form-label">Alamat Instansi</label>
                                <input type="text" class="form-control form-control-sm" id="alamatInstansiPKL"
                                    name="alamatInstansi" placeholder="Alamat Instansi"
                                    value="{{ old('alamatInstansi') }}">
                                <div class="invalid-feedback alamatInstansiPKLError"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="tanggalMulaiPKL" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control form-control-sm" id="tanggalMulaiPKL"
                                    name="tanggalMulai" value="{{ old('tanggalMulai') }}">
                                <div class="invalid-feedback tanggalMulaiPKLError"></div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="tanggalSelesaiPKL" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control form-control-sm" id="tanggalSelesaiPKL"
                                    name="tanggalSelesai" value="{{ old('tanggalSelesai') }}">
                                <div class="invalid-feedback tanggalSelesaiPKLError"></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" id="editButton"><span
                                class="mdi mdi-content-save"></span> Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- MEMPEROLEH DATA TA --}}
    <div class="modal fade" id="memperolehDataTAShowModal" tabindex="-1"
        aria-labelledby="memperolehDataTAShowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="memperolehDataTAShowModalLabel">Edit Formulir Permohonan Data TA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFormMemperolehDataTA">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="dataTAId" name="id">

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="namaInstansiDataTA" class="form-label">Nama Instansi</label>
                                <input type="text" class="form-control form-control-sm" id="namaInstansiDataTA"
                                    name="namaInstansi" placeholder="Nama Instansi" value="{{ old('namaInstansi') }}">
                                <div class="invalid-feedback namaInstansiDataTAError"></div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="alamatInstansiDataTA" class="form-label">Alamat Instansi</label>
                                <input type="text" class="form-control form-control-sm" id="alamatInstansiDataTA"
                                    name="alamatInstansi"
                                    placeholder="Contoh: Jl. Diponegoro No. 12 Kutoarjo, Kutoarjo, Purworejo, Jawa Tengah"
                                    value="{{ old('alamatInstansi') }}">
                                <div class="invalid-feedback alamatInstansiDataTAError"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3">
                                <label for="judulLaporanDataTA" class="form-label">Judul Laporan</label>
                                <input type="text" class="form-control form-control-sm" id="judulLaporanDataTA"
                                    name="judulLaporan" placeholder="Judul Laporan" value="{{ old('judulLaporan') }}">
                                <div class="invalid-feedback judulLaporanDataTAError"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label">Data yang Diminta</label>
                                <div id="dimintaContainerDataTA">
                                    <!-- Input akan ditambahkan secara dinamis -->
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm" id="editButton">
                            <span class="mdi mdi-content-save"></span> Simpan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MEMPEROLEH DATA PKL --}}
    <div class="modal fade" id="memperolehDataPKLShowModal" tabindex="-1"
        aria-labelledby="memperolehDataPKLShowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="memperolehDataPKLShowModalLabel">Edit Formulir Permohonan Data PKL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFormMemperolehDataPKL">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="dataPKLId" name="id">

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="namaInstansiDataPKL" class="form-label">Nama Instansi</label>
                                <input type="text" class="form-control form-control-sm" id="namaInstansiDataPKL"
                                    name="namaInstansi" placeholder="Nama Instansi" value="{{ old('namaInstansi') }}">
                                <div class="invalid-feedback namaInstansiDataPKLError"></div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="alamatInstansiDataPKL" class="form-label">Alamat Instansi</label>
                                <input type="text" class="form-control form-control-sm" id="alamatInstansiDataPKL"
                                    name="alamatInstansi"
                                    placeholder="Contoh: Jl. Diponegoro No. 12 Kutoarjo, Kutoarjo, Purworejo, Jawa Tengah"
                                    value="{{ old('alamatInstansi') }}">
                                <div class="invalid-feedback alamatInstansiDataPKLError"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3">
                                <label for="judulLaporanDataPKL" class="form-label">Judul Laporan</label>
                                <input type="text" class="form-control form-control-sm" id="judulLaporanDataPKL"
                                    name="judulLaporan" placeholder="Judul Laporan" value="{{ old('judulLaporan') }}">
                                <div class="invalid-feedback judulLaporanDataPKLError"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label">Data yang Diminta</label>
                                <div id="dimintaContainerDataPKL">
                                    <!-- Input akan ditambahkan secara dinamis -->
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm" id="editButton">
                            <span class="mdi mdi-content-save"></span> Simpan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="{{ asset('vendors/js/sweetalert2.all.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            // AKTIF KULIAH
            $(document).on('click', '.keteranganAktifEdit', function() {
                let id = $(this).data('id');
                let namaOrangTua = $(this).data('namaOrangTua');
                let alamatOrangTua = $(this).data('alamatOrangTua');
                let pekerjaan = $(this).data('pekerjaan');
                let nip = $(this).data('nip');
                let pangkatAtauGolongan = $(this).data('pangkatAtauGolongan');
                let namaInstansi = $(this).data('namaInstansi');
                let alamatInstansi = $(this).data('alamatInstansi');
                let keperluan = $(this).data('keperluan');


                $('#aktifKuliahIdEdit').val(id);
                $('#namaOrangTuaAktif').val(namaOrangTua);
                $('#alamatOrangTuaAktif').val(alamatOrangTua);
                $('#pekerjaanAktif').val(alamatOrangTua);
                $('#nipAktif').val(nip);
                $('#pangkatAtauGolonganAktif').val(pangkatAtauGolongan);
                $('#namaInstansiAktif').val(namaInstansi);
                $('#alamatInstansiAktif').val(alamatInstansi);
                $('#keperluanAktif').val(keperluan);
            });

            $('#editForm').submit(function(e) {
                e.preventDefault();

                $('input, select').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                let id = $('#aktifKuliahIdEdit').val();
                let namaOrangTuaAktif = $('#namaOrangTuaAktif').val();
                let alamatOrangTuaAktif = $('#alamatOrangTuaAktif').val();
                let pekerjaanAktif = $('#pekerjaanAktif').val();
                let nipAktif = $('#nipAktif').val();
                let pangkatAtauGolonganAktif = $('#pangkatAtauGolonganAktif').val();
                let namaInstansiAktif = $('#namaInstansiAktif').val();
                let alamatInstansiAktif = $('#alamatInstansiAktif').val();
                let keperluanAktif = $('#keperluanAktif').val();
                $.ajax({
                    url: '{{ route('permohonan-surat-update', ':id') }}'.replace(':id', id),
                    type: 'PUT',
                    data: {
                        namaOrangTua: namaOrangTuaAktif,
                        alamatOrangTua: alamatOrangTuaAktif,
                        pekerjaan: pekerjaanAktif,
                        nip: nipAktif,
                        pangkatAtauGolongan: pangkatAtauGolonganAktif,
                        namaInstansi: namaInstansiAktif,
                        alamatInstansi: alamatInstansiAktif,
                        keperluan: keperluanAktif,

                    },
                    success: function(response) {
                        $('#aktifKuliahShowModal').modal('hide');
                        $('#editForm')[0].reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.success,
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            const errors = response.responseJSON.errors;

                            if (errors.namaOrangTua) {
                                $('#namaOrangTuaAktif').addClass('is-invalid');
                                $('.namaOrangTuaAktifError').text(errors.namaOrangTua[0]);
                            }
                            if (errors.alamatOrangTua) {
                                $('#alamatOrangTuaAktif').addClass('is-invalid');
                                $('.alamatOrangTuaAktifError').text(errors.alamatOrangTua[0]);
                            }
                            if (errors.pekerjaan) {
                                $('#pekerjaanAktif').addClass('is-invalid');
                                $('.pekerjaanAktifError').text(errors.pekerjaan[0]);
                            }
                            if (errors.nip) {
                                $('#nipAktif').addClass('is-invalid');
                                $('.nipAktifError').text(errors.nip[0]);
                            }
                            if (errors.pangkatAtauGolongan) {
                                $('#pangkatAtauGolonganAktif').addClass('is-invalid');
                                $('.pangkatAtauGolonganAktifError').text(errors
                                    .pangkatAtauGolongan[0]);
                            }
                            if (errors.pangkatAtauGolongan) {
                                $('#pangkatAtauGolonganAktif').addClass('is-invalid');
                                $('.pangkatAtauGolonganAktifError').text(errors
                                    .pangkatAtauGolongan[0]);
                            }
                            if (errors.namaInstansi) {
                                $('#namaInstansiAktif').addClass('is-invalid');
                                $('.namaInstansiAktifError').text(errors.namaInstansi[0]);
                            }
                            if (errors.alamatInstansi) {
                                $('#alamatInstansiAktif').addClass('is-invalid');
                                $('.alamatInstansiAktifError').text(errors.alamatInstansi[0]);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan. Silakan coba lagi.',
                            });
                        }
                    }
                });
            });


            // CUTI KULIAH
            $(document).on('click', '.cutiKuliahEdit', function() {
                let id = $(this).data('id');
                let masaCuti = $(this).data('masaCuti');
                let alasanCuti = $(this).data('alasanCuti');

                $('#cutiKuliahIdEdit').val(id);
                $('#masaCutiEdit').val(masaCuti);
                $('#alasanCutiCuti').val(alasanCuti);
            });

            $('#editFormCuti').submit(function(e) {
                e.preventDefault();

                $('input, select').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                let id = $('#cutiKuliahIdEdit').val();
                let alasanCutiCuti = $('#alasanCutiCuti').val();

                $.ajax({
                    url: '{{ route('permohonan-surat-update', ':id') }}'.replace(':id', id),
                    type: 'PUT',
                    data: {
                        alasanCuti: alasanCutiCuti,
                    },
                    success: function(response) {
                        $('#cutiKuliahShowModal').modal('hide');
                        $('#editFormCuti')[0].reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.success,
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            const errors = response.responseJSON.errors;
                            if (errors.alasanCuti) {
                                $('#alasanCutiCuti').addClass('is-invalid');
                                $('.alasanCutiCutiError').text(errors.alasanCuti[0]);
                            }
                            if (errors.keperluan) {
                                $('#keperluanCuti').addClass('is-invalid');
                                $('.keperluanCutiError').text(errors.keperluan[0]);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan. Silakan coba lagi.',
                            });
                        }
                    }
                });
            });


            // PINDAH KELAS
            $(document).on('click', '.pindahKelasEdit', function() {
                let id = $(this).data('id');
                let kelasAsal = $(this).data('kelasAsal');
                let kelasTujuan = $(this).data('kelasTujuan');

                $('#pindahKelasId').val(id);
                $('#kelasAsalEdit').val(kelasAsal);
                $('#kelasTujuanEdit').val(kelasTujuan);
            });

            $('#editFormPindahKelas').submit(function(e) {
                e.preventDefault();

                $('input, select').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                let id = $('#pindahKelasId').val();
                let kelasAsal = $('#kelasAsalEdit').val();
                let kelasTujuan = $('#kelasTujuanEdit').val();

                $.ajax({
                    url: '{{ route('permohonan-surat-update', ':id') }}'.replace(':id', id),
                    type: 'PUT',
                    data: {
                        kelasAsal: kelasAsal,
                        kelasTujuan: kelasTujuan,
                    },
                    success: function(response) {
                        $('#pindahKelasShowModal').modal('hide');
                        $('#editFormPindahKelas')[0].reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.success,
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            const errors = response.responseJSON.errors;
                            if (errors.kelasAsal) {
                                $('#kelasAsalEdit').addClass('is-invalid');
                                $('.kelasAsalError').text(errors.kelasAsal[0]);
                            }
                            if (errors.kelasTujuan) {
                                $('#kelasTujuanEdit').addClass('is-invalid');
                                $('.kelasTujuanError').text(errors.kelasTujuan[0]);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan. Silakan coba lagi.',
                            });
                        }
                    }
                });
            });

            // PINDAH PT
            $(document).on('click', '.pindahPTEdit', function() {
                let id = $(this).data('id');
                let ptAsal = $(this).data('ptAsal');
                let ptTujuan = $(this).data('ptTujuan');
                let statusAkreditasi = $(this).data('statusAkreditasi');

                $('#pindahPTId').val(id);
                $('#ptAsalEdit').val(ptAsal);
                $('#ptTujuanEdit').val(ptTujuan);
                $('#statusAkreditasiEdit').val(statusAkreditasi);
            });

            $('#editFormPindahPT').submit(function(e) {
                e.preventDefault();

                $('input, select').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                let id = $('#pindahPTId').val();
                let ptAsal = $('#ptAsalEdit').val();
                let ptTujuan = $('#ptTujuanEdit').val();
                let statusAkreditasi = $('#statusAkreditasiEdit').val();

                $.ajax({
                    url: '{{ route('permohonan-surat-update', ':id') }}'.replace(':id', id),
                    type: 'PUT',
                    data: {
                        ptAsal: ptAsal,
                        ptTujuan: ptTujuan,
                        statusAkreditasi: statusAkreditasi,
                    },
                    success: function(response) {
                        $('#pindahPTShowModal').modal('hide');
                        $('#editFormPindahPT')[0].reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.success,
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            const errors = response.responseJSON.errors;
                            if (errors.ptAsal) {
                                $('#ptAsalEdit').addClass('is-invalid');
                                $('.ptAsalError').text(errors.ptAsal[0]);
                            }
                            if (errors.ptTujuan) {
                                $('#ptTujuanEdit').addClass('is-invalid');
                                $('.ptTujuanError').text(errors.ptTujuan[0]);
                            }
                            if (errors.statusAkreditasi) {
                                $('#statusAkreditasiEdit').addClass('is-invalid');
                                $('.statusAkreditasiEditError').text(errors.statusAkreditasi[
                                    0]);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan. Silakan coba lagi.',
                            });
                        }
                    }
                });
            });

            // IJIN PKL
            $(document).on('click', '.ijinPKLEdit', function() {
                let id = $(this).data('id');
                let namaInstansi = $(this).data('namaInstansi');
                let pimpinan = $(this).data('pimpinan');
                let alamatInstansi = $(this).data('alamatInstansi');
                let tanggalMulai = $(this).data('tanggalMulai');
                let tanggalSelesai = $(this).data('tanggalSelesai');
                console.log(id, namaInstansi, pimpinan, alamatInstansi, tanggalMulai, tanggalSelesai);

                $('#ijinPKLId').val(id);
                $('#namaInstansiPKL').val(namaInstansi);
                $('#pimpinanPKL').val(pimpinan);
                $('#alamatInstansiPKL').val(alamatInstansi);
                $('#tanggalMulaiPKL').val(tanggalMulai);
                $('#tanggalSelesaiPKL').val(tanggalSelesai);
            });

            $('#editFormijinPKL').submit(function(e) {
                e.preventDefault();

                $('input, select').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                let id = $('#ijinPKLId').val();
                let namaInstansi = $('#namaInstansiPKL').val();
                let pimpinan = $('#pimpinanPKL').val();
                let alamatInstansi = $('#alamatInstansiPKL').val();
                let tanggalMulai = $('#tanggalMulaiPKL').val();
                let tanggalSelesai = $('#tanggalSelesaiPKL').val();

                $.ajax({
                    url: '{{ route('permohonan-surat-update', ':id') }}'.replace(':id', id),
                    type: 'PUT',
                    data: {
                        namaInstansi: namaInstansi,
                        pimpinan: pimpinan,
                        alamatInstansi: alamatInstansi,
                        tanggalMulai: tanggalMulai,
                        tanggalSelesai: tanggalSelesai,
                    },
                    success: function(response) {
                        $('#ijinPKLShowModal').modal('hide');
                        $('#editFormijinPKL')[0].reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.success,
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            const errors = response.responseJSON.errors;
                            if (errors.namaInstansi) {
                                $('#namaInstansiPKL').addClass('is-invalid');
                                $('.namaInstansiPKLError').text(errors.namaInstansi[0]);
                            }
                            if (errors.pimpinan) {
                                $('#pimpinanPKL').addClass('is-invalid');
                                $('.pimpinanPKLError').text(errors.pimpinan[0]);
                            }
                            if (errors.alamatInstansi) {
                                $('#alamatInstansiPKL').addClass('is-invalid');
                                $('.alamatInstansiPKLError').text(errors.alamatInstansi[0]);
                            }
                            if (errors.tanggalMulai) {
                                $('#tanggalMulaiPKL').addClass('is-invalid');
                                $('.tanggalMulaiPKLError').text(errors.tanggalMulai[0]);
                            }
                            if (errors.tanggalSelesai) {
                                $('#tanggalSelesaiPKL').addClass('is-invalid');
                                $('.tanggalSelesaiPKLError').text(errors.tanggalSelesai[0]);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...!',
                                text: 'Terjadi kesalahan. Silakan coba lagi.',
                            });
                        }
                    }
                });
            });

            // MEMPEROLEH DATA TA
            $(document).on('click', '.editDataTA', function() {
                let id = $(this).data('id');
                let namaInstansi = $(this).data('nama-instansi');
                let alamatInstansi = $(this).data('alamat-instansi');
                let judulLaporan = $(this).data('judul-laporan');
                let rawDataDiminta = $(this).data('diminta');

                let dataDiminta = [];

                if (rawDataDiminta !== null && rawDataDiminta !== undefined) {
                    if (Array.isArray(rawDataDiminta) && rawDataDiminta.every(item => typeof item ===
                            'string')) {
                        dataDiminta = rawDataDiminta;
                    } else if (typeof rawDataDiminta === 'string') {
                        try {
                            const parsed = JSON.parse(rawDataDiminta);
                            dataDiminta = Array.isArray(parsed) ? parsed : [rawDataDiminta];
                        } catch (e) {
                            dataDiminta = [rawDataDiminta];
                        }
                    } else if (Array.isArray(rawDataDiminta) && rawDataDiminta.length === 1 &&
                        typeof rawDataDiminta[0] === 'string') {
                        try {
                            const innerParsed = JSON.parse(rawDataDiminta[0]);
                            dataDiminta = Array.isArray(innerParsed) ? innerParsed : rawDataDiminta;
                        } catch (e) {
                            dataDiminta = rawDataDiminta;
                        }
                    } else if (Array.isArray(rawDataDiminta)) {
                        dataDiminta = rawDataDiminta;
                    } else {
                        dataDiminta = [String(rawDataDiminta)];
                    }
                }

                $('#dataTAId').val(id);
                $('#namaInstansiDataTA').val(namaInstansi);
                $('#alamatInstansiDataTA').val(alamatInstansi);
                $('#judulLaporanDataTA').val(judulLaporan);

                let container = $('#dimintaContainerDataTA');
                container.empty();

                if (!Array.isArray(dataDiminta)) {
                    dataDiminta = [];
                }

                if (dataDiminta.length > 0) {
                    dataDiminta.forEach((data, index) => {
                        const safeValue = $('<div>').text(data).html();
                        const inputGroup = `
                            <div class="mb-2">
                                <div class="input-group">
                                    <input type="text" name="dataDimintaTA[]" class="form-control form-control-sm"
                                        placeholder="Masukkan data yang diminta" value="${safeValue}">
                                    ${index === 0 
                                        ? '<button type="button" class="btn btn-primary btn-sm tambahDataTA"><i class="mdi mdi-plus"></i></button>' 
                                        : '<button type="button" class="btn btn-secondary btn-sm kurangDataTA"><i class="mdi mdi-minus"></i></button>'
                                    }
                                </div>
                                <div class="invalid-feedback dataDimintaTAError"></div>
                            </div>`;
                        container.append(inputGroup);
                    });
                } else {
                    container.append(`
                        <div class="mb-2">
                            <div class="input-group">
                                <input type="text" name="dataDimintaTA[]" class="form-control form-control-sm"
                                    placeholder="Masukkan data yang diminta">
                                <button type="button" class="btn btn-primary btn-sm tambahDataTA"><i class="mdi mdi-plus"></i></button>
                            </div>
                            <div class="invalid-feedback dataDimintaTAError"></div>
                        </div>
                    `);
                }


                $('#memperolehDataTAShowModal').modal('show');
            });

            $(document).on('click', '.tambahDataTA', function() {
                let inputGroup = `
                    <div class="mb-2">
                        <div class="input-group">
                            <input type="text" name="dataDimintaTA[]" class="form-control form-control-sm"
                                placeholder="Masukkan data yang diminta">
                            <button type="button" class="btn btn-secondary btn-sm kurangDataTA"><i class="mdi mdi-minus"></i></button>
                        </div>
                        <div class="invalid-feedback dataDimintaTAError"></div>
                    </div>`;
                $('#dimintaContainerDataTA').append(inputGroup);
            });


            $(document).on('click', '.kurangDataTA', function() {
                const container = $('#dimintaContainerDataTA');

                if (container.find('.input-group').length > 1) {
                    $(this).closest('.mb-2').remove();
                } else {
                    $(this).closest('.input-group').find('input').val('').removeClass('is-invalid');
                    $(this).closest('.mb-2').find('.dataDimintaTAError').text('').hide();
                }
            });


            $(document).on('input', "input[name='dataDimintaTA[]']", function() {
                $(this).removeClass('is-invalid');
                $(this).closest('.mb-2').find('.dataDimintaTAError').text('').hide();
            });


            $('#editFormMemperolehDataTA').submit(function(e) {
                e.preventDefault();

                $('input, select').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                let id = $('#dataTAId').val();
                let namaInstansi = $('#namaInstansiDataTA').val();
                let alamatInstansi = $('#alamatInstansiDataTA').val();
                let judulLaporan = $('#judulLaporanDataTA').val();
                let dataDiminta = $("input[name='dataDimintaTA[]']").map(function() {
                    return $(this).val().trim();
                }).get().filter(item => item !== '');

                $.ajax({
                    url: '{{ route('permohonan-surat-update', ':id') }}'.replace(':id', id),
                    type: 'PUT',
                    data: {
                        namaInstansi: namaInstansi,
                        alamatInstansi: alamatInstansi,
                        judulLaporan: judulLaporan,
                        dataDiminta: dataDiminta
                    },
                    success: function(response) {
                        $('#memperolehDataTAShowModal').modal('hide');
                        $('#editFormMemperolehDataTA')[0].reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.success,
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            const errors = response.responseJSON.errors;
                            if (errors.namaInstansi) {
                                $('#namaInstansiDataTA').addClass('is-invalid');
                                $('.namaInstansiDataTAError').text(errors.namaInstansi[0]);
                            }
                            if (errors.alamatInstansi) {
                                $('#alamatInstansiDataTA').addClass('is-invalid');
                                $('.alamatInstansiDataTAError').text(errors.alamatInstansi[0]);
                            }
                            if (errors.judulLaporan) {
                                $('#judulLaporanDataTA').addClass('is-invalid');
                                $('.judulLaporanDataTAError').text(errors.judulLaporan[0]);
                            }
                            if (errors.dataDiminta) {
                                $("input[name='dataDimintaTA[]']").each(function() {
                                    $(this).addClass('is-invalid');
                                    $(this).closest('.mb-2').find('.dataDimintaTAError')
                                        .text(errors.dataDiminta[0]).show();
                                });
                            }

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan. Silakan coba lagi.',
                            });
                        }
                    }
                });
            });


            // MEMPEROLEH DATA PKL
            $(document).on('click', '.editDataPKL', function() {
                let id = $(this).data('id');
                let namaInstansi = $(this).data('nama-instansi');
                let alamatInstansi = $(this).data('alamat-instansi');
                let judulLaporan = $(this).data('judul-laporan');
                let rawDataDiminta = $(this).data('diminta');

                let dataDiminta = [];

                if (rawDataDiminta !== null && rawDataDiminta !== undefined) {
                    if (Array.isArray(rawDataDiminta) && rawDataDiminta.every(item => typeof item ===
                            'string')) {
                        dataDiminta = rawDataDiminta;
                    } else if (typeof rawDataDiminta === 'string') {
                        try {
                            const parsed = JSON.parse(rawDataDiminta);
                            dataDiminta = Array.isArray(parsed) ? parsed : [rawDataDiminta];
                        } catch (e) {
                            dataDiminta = [rawDataDiminta];
                        }
                    } else if (Array.isArray(rawDataDiminta) && rawDataDiminta.length === 1 &&
                        typeof rawDataDiminta[0] === 'string') {
                        try {
                            const innerParsed = JSON.parse(rawDataDiminta[0]);
                            dataDiminta = Array.isArray(innerParsed) ? innerParsed : rawDataDiminta;
                        } catch (e) {
                            dataDiminta = rawDataDiminta;
                        }
                    } else if (Array.isArray(rawDataDiminta)) {
                        dataDiminta = rawDataDiminta;
                    } else {
                        dataDiminta = [String(rawDataDiminta)];
                    }
                }

                $('#dataPKLId').val(id);
                $('#namaInstansiDataPKL').val(namaInstansi);
                $('#alamatInstansiDataPKL').val(alamatInstansi);
                $('#judulLaporanDataPKL').val(judulLaporan);

                let container = $('#dimintaContainerDataPKL');
                container.empty();

                if (!Array.isArray(dataDiminta)) {
                    dataDiminta = [];
                }

                if (dataDiminta.length > 0) {
                    dataDiminta.forEach((data, index) => {
                        const safeValue = $('<div>').text(data).html();
                        const inputGroup = `
                            <div class="mb-2">
                                <div class="input-group">
                                    <input type="text" name="dataDimintaPKL[]" class="form-control form-control-sm"
                                        placeholder="Masukkan data yang diminta" value="${safeValue}">
                                    ${index === 0 
                                        ? '<button type="button" class="btn btn-primary btn-sm tambahDataPKL"><i class="mdi mdi-plus"></i></button>' 
                                        : '<button type="button" class="btn btn-secondary btn-sm kurangDataPKL"><i class="mdi mdi-minus"></i></button>'
                                    }
                                </div>
                                <div class="invalid-feedback dataDimintaPKLError"></div>
                            </div>`;
                        container.append(inputGroup);
                    });
                } else {
                    container.append(`
                        <div class="mb-2">
                            <div class="input-group">
                                <input type="text" name="dataDimintaPKL[]" class="form-control form-control-sm"
                                    placeholder="Masukkan data yang diminta">
                                <button type="button" class="btn btn-primary btn-sm tambahDataPKL"><i class="mdi mdi-plus"></i></button>
                            </div>
                            <div class="invalid-feedback dataDimintaPKLError"></div>
                        </div>
                    `);
                }

                $('#memperolehDataPKLShowModal').modal('show');
            });


            $(document).on('click', '.tambahDataPKL', function() {
                let inputGroup = `
                    <div class="mb-2">
                        <div class="input-group">
                            <input type="text" name="dataDimintaPKL[]" class="form-control form-control-sm"
                                placeholder="Masukkan data yang diminta">
                            <button type="button" class="btn btn-secondary btn-sm kurangDataPKL"><i class="mdi mdi-minus"></i></button>
                        </div>
                        <div class="invalid-feedback dataDimintaPKLError"></div>
                    </div>`;
                $('#dimintaContainerDataPKL').append(inputGroup);
            });


            $(document).on('click', '.kurangDataPKL', function() {
                const container = $('#dimintaContainerDataPKL');

                if (container.find('.input-group').length > 1) {
                    $(this).closest('.mb-2').remove();
                } else {
                    $(this).closest('.input-group').find('input').val('').removeClass('is-invalid');
                    $(this).closest('.mb-2').find('.dataDimintaPKLError').text('').hide();
                }
            });

            $(document).on('input', "input[name='dataDimintaPKL[]']", function() {
                $(this).removeClass('is-invalid');
                $(this).closest('.mb-2').find('.dataDimintaPKLError').text('').hide();
            });
            $('#editFormMemperolehDataPKL').submit(function(e) {
                e.preventDefault();

                $('input, select').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                let id = $('#dataPKLId').val();
                let namaInstansi = $('#namaInstansiDataPKL').val();
                let alamatInstansi = $('#alamatInstansiDataPKL').val();
                let judulLaporan = $('#judulLaporanDataPKL').val();
                let dataDiminta = $("input[name='dataDimintaPKL[]']").map(function() {
                    return $(this).val().trim();
                }).get().filter(item => item !== '');

                $.ajax({
                    url: '{{ route('permohonan-surat-update', ':id') }}'.replace(':id', id),
                    type: 'PUT',
                    data: {
                        namaInstansi: namaInstansi,
                        alamatInstansi: alamatInstansi,
                        judulLaporan: judulLaporan,
                        dataDiminta: dataDiminta
                    },
                    success: function(response) {
                        $('#memperolehDataPKLShowModal').modal('hide');
                        $('#editFormMemperolehDataPKL')[0].reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.success,
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            const errors = response.responseJSON.errors;
                            if (errors.namaInstansi) {
                                $('#namaInstansiDataPKL').addClass('is-invalid');
                                $('.namaInstansiDataPKLError').text(errors.namaInstansi[0]);
                            }
                            if (errors.alamatInstansi) {
                                $('#alamatInstansiDataPKL').addClass('is-invalid');
                                $('.alamatInstansiDataPKLError').text(errors.alamatInstansi[0]);
                            }
                            if (errors.judulLaporan) {
                                $('#judulLaporanDataPKL').addClass('is-invalid');
                                $('.judulLaporanDataPKLError').text(errors.judulLaporan[0]);
                            }
                            if (errors.dataDiminta) {
                                $("input[name='dataDimintaPKL[]']").each(function() {
                                    $(this).addClass('is-invalid');
                                    $(this).closest('.mb-2').find(
                                        '.dataDimintaPKLError').text(errors
                                        .dataDiminta[0]).show();
                                });
                            }

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan. Silakan coba lagi.',
                            });
                        }
                    }
                });
            });


            $(document).on('click', '.delete-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data Permohonan ini akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('permohonan-surat-delete', ':id') }}'.replace(
                                ':id',
                                id),
                            method: 'DELETE',
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sukses!',
                                    text: response.success,
                                    confirmButtonText: 'Oke'
                                }).then(() => {
                                    location
                                        .reload();
                                });
                            },
                            error: function(response) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Terjadi kesalahan saat menghapus data. Silakan coba lagi.',
                                });
                            }
                        });
                    }
                });
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector("#dataDimintaContainerTA").addEventListener("click", function(e) {
                if (e.target.classList.contains("addDataTA")) {
                    let newInput = document.createElement("div");
                    newInput.classList.add("input-group", "mb-2");
                    newInput.innerHTML = `
            <input type="text" name="dataDimintaTA[]" class="form-control form-control-sm" placeholder="Masukkan data yang diminta">
            <button type="button" class="btn btn-secondary btn-sm removeDataTA"><i class="mdi mdi-minus"></i></button>
        `;
                    this.appendChild(newInput);
                } else if (e.target.classList.contains("removeDataTA")) {
                    const inputGroups = this.querySelectorAll(".input-group");
                    if (inputGroups.length > 1) {
                        e.target.parentElement.remove();
                    } else {
                        e.target.parentElement.querySelector("input").value = "";
                    }
                }
            });

            document.querySelector("#dataDimintaContainerPKL").addEventListener("click", function(e) {
                if (e.target.classList.contains("addDataPKL")) {
                    let newInput = document.createElement("div");
                    newInput.classList.add("input-group", "mb-2");
                    newInput.innerHTML = `
            <input type="text" name="dataDimintaPKL[]" class="form-control form-control-sm" placeholder="Masukkan data yang diminta">
            <button type="button" class="btn btn-secondary btn-sm removeDataPKL"><i class="mdi mdi-minus"></i></button>
        `;
                    this.appendChild(newInput);
                } else if (e.target.classList.contains("removeDataPKL")) {
                    const inputGroups = this.querySelectorAll(".input-group");
                    if (inputGroups.length > 1) {
                        e.target.parentElement.remove();
                    } else {
                        e.target.parentElement.querySelector("input").value = "";
                    }
                }
            });

        });

        function showForm() {
            let jenis = document.getElementById('jenisSurat').value;

            document.getElementById('ketAktifKuliah').classList.add('hidden');
            document.getElementById('cutiKuliah').classList.add('hidden');
            document.getElementById('pindahKelas').classList.add('hidden');
            document.getElementById('pindahPT').classList.add('hidden');
            document.getElementById('mengundurkanDiri').classList.add('hidden');
            document.getElementById('ijinPKL').classList.add('hidden');
            document.getElementById('memperolehDataTA').classList.add('hidden');
            document.getElementById('memperolehDataPKL').classList.add('hidden');

            if (jenis === 'aktifKuliah') {
                document.getElementById('ketAktifKuliah').classList.remove('hidden');
            } else if (jenis === 'cutiKuliah') {
                document.getElementById('cutiKuliah').classList.remove('hidden');
            } else if (jenis === 'pindahKelas') {
                document.getElementById('pindahKelas').classList.remove('hidden');
            } else if (jenis === 'pindahPT') {
                document.getElementById('pindahPT').classList.remove('hidden');
            } else if (jenis === 'mengundurkanDiri') {
                document.getElementById('mengundurkanDiri').classList.remove('hidden');
            } else if (jenis === 'ijinPKL') {
                document.getElementById('ijinPKL').classList.remove('hidden');
            } else if (jenis === 'memperolehDataTA') {
                document.getElementById('memperolehDataTA').classList.remove('hidden');
            } else if (jenis === 'memperolehDataPKL') {
                document.getElementById('memperolehDataPKL').classList.remove('hidden');
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            @if (session('selected_form'))
                document.getElementById('jenisSurat').value = "{{ session('selected_form') }}";
            @endif

            showForm();
        });
    </script>

    <style>
        .hidden {
            display: none;
        }
    </style>

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
