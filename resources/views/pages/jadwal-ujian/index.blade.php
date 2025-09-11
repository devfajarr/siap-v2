@extends('layouts.main')

@section('container')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="breadcrumb">
                <a href="/presensi/dashboard" class="breadcrumb-item">
                    <span class="mdi mdi-home"></span> Dashboard
                </a>
                <span class="breadcrumb-item" id="dataMasterBreadcrumb">Jadwal Ujian</span>
            </div>
            <div class="row">
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#tambahModal">
                                    <span class="mdi mdi-plus"></span> Tambah
                                </button>

                                <div class="d-flex align-items-center ms-auto">
                                    <div class="me-2">
                                        <button type="button" id="filterDropdown"
                                            class="btn btn-outline-secondary btn-sm border-0" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <span class="mdi mdi-filter"></span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="filterDropdown"
                                            style="min-width:280px;">
                                            <div class="mb-3">
                                                <label for="pengawasFilter" class="form-label">Pengawas</label>
                                                <select id="pengawasFilter" class="form-select">
                                                    <option value="">Pengawas</option>
                                                    @foreach ($pengawass as $pengawas)
                                                        <option value="{{ $pengawas->id }}">{{ $pengawas->nama }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-2">
                                                <label for="kelasFilter" class="form-label">Kelas</label>
                                                <select id="kelasFilter" class="form-select">
                                                    <option value="">Semua Kelas</option>
                                                    @foreach ($kelass as $kelas)
                                                        <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-2">
                                                <label for="jenisFilter" class="form-label">Jenis</label>
                                                <select id="jenisFilter" class="form-select">
                                                    <option value="">Semua Jenis</option>
                                                    <option value="uts">Ujian Tengah Semester</option>
                                                    <option value="uas">Ujian Akhir Semester</option>
                                                </select>
                                            </div>

                                            <div class="d-grid gap-2 mt-3">
                                                <button type="button" class="btn btn-primary btn-sm" id="applyFilter">
                                                    Terapkan Filter
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="input-group input-group-sm" style="width: 200px;">
                                        <input type="text" id="search" class="form-control"
                                            placeholder="Cari...">
                                        <button class="btn btn-outline-secondary" type="button" id="clearSearchButton">
                                            <span class="mdi mdi-close"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Mata Kuliah</th>
                                            <th>Pengawas</th>
                                            <th>Jenis</th>
                                            <th>Ruangan</th>
                                            <th>Kelas</th>
                                            <th>Tanggal</th>
                                            <th>Jam</th>
                                            <th>Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($jadwals as $jadwal)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $jadwal->matkul->nama_matkul }}</td>
                                                <td>{{ $jadwal->pegawai->nama }}</td>
                                                <td>{{ $jadwal->jenis_ujian == 'uts' ? 'Ujian Tengah Semester' : 'Ujian Akhir Semester' }}
                                                </td>
                                                <td>{{ $jadwal->ruangan->nama }}</td>
                                                <td>{{ $jadwal->kelas->nama_kelas }}</td>
                                                <td>{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') }}
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($jadwal->waktu_selesai)->format('H:i') }}</td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm editButton"
                                                        data-id="{{ $jadwal->id }}"
                                                        data-matkul="{{ $jadwal->matkul->id }}"
                                                        data-pengawas="{{ $jadwal->pegawai->id }}"
                                                        data-kelas="{{ $jadwal->kelas_id }}"
                                                        data-semester="{{ $jadwal->kelas->id_semester }}"
                                                        data-prodi="{{ $jadwal->kelas->id_prodi }}"
                                                        data-ruangan="{{ $jadwal->ruangan->id }}"
                                                        data-tahun="{{ $jadwal->tahun }}"
                                                        data-jam_mulai="{{ $jadwal->waktu_mulai }}"
                                                        data-jam_selesai="{{ $jadwal->waktu_selesai }}"
                                                        data-tanggal="{{ $jadwal->tanggal }}"
                                                        data-jenis-ujian="{{ $jadwal->jenis_ujian }}"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        <span class="mdi mdi-pencil"></span> Edit
                                                    </button>
                                                    <button class="btn btn-danger btn-sm deleteButton"
                                                        data-id="{{ $jadwal->id }}">
                                                        <span class="mdi mdi-delete"></span> Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">Jadwal belum ditambahkan</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bootstrap untuk Tambah Data -->
    <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahModalLabel">Tambah Jadwal Mengajar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="tambahForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="kelas" class="form-label">Kelas <span
                                            style="color: red;">*</span></label>
                                    <select class="form-select" id="kelas" name="kelas">
                                        <option selected>--Kelas--</option>
                                        @foreach ($kelass as $kelas)
                                            <option value="{{ $kelas->id }}"
                                                data-semester-id="{{ $kelas->id_semester }}"
                                                data-prodi-id="{{ $kelas->id_prodi }}">
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="kelasError"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tahun" class="form-label">Tahun Akademik <span
                                            style="color: red;">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="tahun"
                                        name="tahun" disabled value="{{ $tahun->tahun_akademik }}">
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="pengawas" class="form-label">Pengawas <span
                                            style="color: red;">*</span></label>
                                    <select class="form-select" id="pengawas" name="pengawas">
                                        <option selected>--Pengawas--</option>
                                        @foreach ($pengawass as $pengawas)
                                            <option value="{{ $pengawas->id }}">{{ $pengawas->nama }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="pengawasError"></div>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="matkul" class="form-label">Matkul <span
                                            style="color: red;">*</span></label>
                                    <select class="form-select" id="matkul" name="matkul">
                                        <option selected>--Matkul--</option>
                                        @foreach ($matkuls as $matkul)
                                            <option value="{{ $matkul->id }}"
                                                data-semester-id="{{ $matkul->semester_id }}"
                                                data-prodi-id="{{ $matkul->prodi_id }}"
                                                data-teori="{{ $matkul->teori }}" data-praktek="{{ $matkul->praktek }}">
                                                {{ $matkul->nama_matkul }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="namaMatkulError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ruangan" class="form-label">Ruangan <span
                                            style="color: red;">*</span></label>
                                    <select class="form-select" id="ruangan" name="ruangan">
                                        <option selected>--Ruangan--</option>
                                        @foreach ($ruangans as $ruangan)
                                            <option value="{{ $ruangan->id }}">{{ $ruangan->nama }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="ruanganError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jamMulai" class="form-label">Jam Mulai <span
                                            style="color: red;">*</span></label>
                                    <input type="time" class="form-control form-control-sm" id="jamMulai"
                                        name="jam_mulai">
                                    <div class="invalid-feedback" id="jamMulaiError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jamSelesai" class="form-label">Jam Selesai <span
                                            style="color: red;">*</span></label>
                                    <input type="time" class="form-control form-control-sm" id="jamSelesai"
                                        name="jam_selesai">
                                    <div class="invalid-feedback" id="jamSelesaiError"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal <span style="color: red;">*</span></label>
                                    <input type="date" class="form-control form-control-sm" id="tanggal"
                                        name="tanggal">
                                    <div class="invalid-feedback" id="tanggalError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenisUjian" class="form-label">Jenis Ujian <span
                                            style="color: red;">*</span></label>
                                    <select class="form-select" id="jenisUjian" name="jenisUjian">
                                        <option selected value="">--Jenis Ujian--</option>
                                        <option value="uts">Ujian Tengah semester</option>
                                        <option value="uas">Ujian Akhir semester</option>
                                    </select>
                                    <div class="invalid-feedback" id="jenisUjianError"></div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><span class="mdi mdi-content-save"></span>
                            Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Jadwal Ujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editid">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="editKelas" class="form-label">Kelas <span
                                            style="color: red;">*</span></label>
                                    <select class="form-select" id="editKelas" name="kelas" required>
                                        <option selected>--Kelas--</option>
                                        @foreach ($kelass as $kelas)
                                            <option value="{{ $kelas->id }}"
                                                data-semester-id="{{ $kelas->id_semester }}"
                                                data-prodi-id="{{ $kelas->id_prodi }}"
                                                {{ old('kelas', $kelas->id) == $kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="editKelasError"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tahunEdit" class="form-label">Tahun Akademik <span
                                            style="color: red;">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="tahunEdit"
                                        name="tahunEdit" disabled>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="editPengawas" class="form-label">Pengawas <span
                                            style="color: red;">*</span></label>
                                    <select class="form-select" id="editPengawas" name="editPengawas" required>
                                        <option selected>--Pengawas--</option>
                                        @foreach ($pengawass as $pengawas)
                                            <option value="{{ $pengawas->id }}">{{ $pengawas->nama }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="editPengawasError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editMatkul" class="form-label">Matkul <span
                                            style="color: red;">*</span></label>
                                    <select class="form-select" id="editMatkul" name="matkul" required>
                                        <option selected>--Matkul--</option>
                                        @foreach ($matkuls as $matkul)
                                            <option value="{{ $matkul->id }}" data-teori="{{ $matkul->teori }}"
                                                data-praktek="{{ $matkul->praktek }}" data-teori="{{ $matkul->teori }}"
                                                data-semester-id="{{ $matkul->semester_id }}"
                                                data-prodi-id="{{ $matkul->prodi_id }}">
                                                {{ $matkul->nama_matkul }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="editNamaMatkulError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editRuangan" class="form-label">Ruangan <span
                                            style="color: red;">*</span></label>
                                    <select class="form-select" id="editRuangan" name="ruangan" required>
                                        <option selected>--Ruangan--</option>
                                        @foreach ($ruangans as $ruangan)
                                            <option value="{{ $ruangan->id }}">{{ $ruangan->nama }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="editRuanganError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editJamMulai" class="form-label">Jam Mulai <span
                                            style="color: red;">*</span></label>
                                    <input type="time" class="form-control form-control-sm" id="editJamMulai"
                                        name="jam_mulai" required>
                                    <div class="invalid-feedback" id="editJamMulaiError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editJamSelesai" class="form-label">Jam Selesai <span
                                            style="color: red;">*</span></label>
                                    <input type="time" class="form-control form-control-sm" id="editJamSelesai"
                                        name="jam_selesai" required>
                                    <div class="invalid-feedback" id="editJamSelesaiError"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="editTanggal">Tanggal <span
                                            style="color: red;">*</span></label>
                                    <input type="date" class="form-control form-control-sm" id="editTanggal"
                                        name="EditTanggal">
                                    <div class="invalid-feedback" id="editTanggalError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenisUjian" class="form-label">Jenis Ujian <span
                                            style="color: red;">*</span></label>
                                    <select class="form-select" id="editjenisUjian" name="editjenisUjian">
                                        <option selected value="">--Jenis Ujian--</option>
                                        <option value="uts">Ujian Tengah semester</option>
                                        <option value="uas">Ujian Akhir semester</option>
                                    </select>
                                    <div class="invalid-feedback" id="editjenisUjianError"></div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" id="editButton"><span
                                class="mdi mdi-content-save"></span> Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // $('#matkul').change(function() {
            //     const selectedOption = $(this).find('option:selected');
            //     const teori = parseInt(selectedOption.data('teori')) || 0;
            //     const praktek = parseInt(selectedOption.data('praktek')) || 0;
            //     const totalSKS = teori + praktek;
            //     $('#sks').val(totalSKS);
            // });

            $('#matkul')
                .val('--Matkul--')
                .prop('disabled', true);
            $('#kelas').on('change', function() {
                const selectedKelasId = $(this).val();

                const semesterId = $(this).find(':selected').data('semester-id');
                const prodiId = $(this).find(':selected').data('prodi-id');

                $('#matkul')
                    .val('--Matkul--')
                    .prop('disabled', false);

                $('#matkul option').each(function() {
                    const matkulSemesterId = $(this).data('semester-id');
                    const matkulProdiId = $(this).data('prodi-id');

                    if (matkulSemesterId === semesterId && matkulProdiId === prodiId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });


            $('#tambahForm').submit(function(e) {
                e.preventDefault();

                $('input, select, textarea').removeClass('is-invalid');
                $('.invalid-feedback').text('');


                let matkul_id = $('#matkul').val();
                let pengawas_id = $('#pengawas').val();
                let ruangan_id = $('#ruangan').val();
                let kelas_id = $('#kelas').val();
                let tahun = $('#tahun').val();
                let jam_mulai = $('#jamMulai').val();
                let jam_selesai = $('#jamSelesai').val();
                let tanggal = $('#tanggal').val();
                let jenis_ujian = $('#jenisUjian').val();
                $.ajax({
                    url: '{{ route('jadwal-ujian.store') }}',
                    method: 'POST',
                    data: {
                        matkul_id: matkul_id,
                        pengawas_id: pengawas_id,
                        ruangan_id: ruangan_id,
                        kelas_id: kelas_id,
                        tahun: tahun,
                        waktu_mulai: jam_mulai,
                        waktu_selesai: jam_selesai,
                        tanggal: tanggal,
                        jenis_ujian: jenis_ujian,

                    },

                    success: function(response) {
                        $('#tambahModal').modal('hide');
                        $('#tambahForm')[0].reset();

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

                            if (errors.matkul_id) {
                                $('#matkul').addClass('is-invalid');
                                $('#namaMatkulError').text(errors.matkul_id[0]);
                            }
                            if (errors.pengawas_id) {
                                $('#pengawas').addClass('is-invalid');
                                $('#pengawasError').text(errors.pengawas_id[0]);
                            }
                            if (errors.ruangan_id) {
                                $('#ruangan').addClass('is-invalid');
                                $('#ruanganError').text(errors.ruangan_id[0]);
                            }
                            if (errors.kelas_id) {
                                $('#kelas').addClass('is-invalid');
                                $('#kelasError').text(errors.kelas_id[0]);
                            }

                            if (errors.waktu_selesai) {
                                $('#jamSelesai').addClass('is-invalid');
                                $('#jamSelesaiError').text(errors.waktu_selesai[0]);
                            }

                            if (errors.waktu_mulai) {
                                $('#jamMulai').addClass('is-invalid');
                                $('#jamMulaiError').text(errors.waktu_mulai[0]);
                            }
                            if (errors.tanggal) {
                                $('#tanggal').addClass('is-invalid');
                                $('#tanggalError').text(errors.tanggal[0]);
                            }
                            if (errors.jenis_ujian) {
                                $('#jenisUjian').addClass('is-invalid');
                                $('#jenisUjianError').text(errors.jenis_ujian[0]);
                            }


                        } else if (response.status === 400) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.responseJSON.error ||
                                    'Terjadi kesalahan. Silakan coba lagi.',
                            });
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

            $('#editModal').on('show.bs.modal', function(event) {
                let button = $(event.relatedTarget);
                let jadwalId = button.data('id');
                let matkulId = button.data('matkul');
                let pengawasId = button.data('pengawas');
                let kelasId = button.data('kelas');
                let ruanganId = button.data('ruangan');
                let jamMulai = button.data('jam_mulai');
                let jamSelesai = button.data('jam_selesai');
                let id = button.data('id');
                let tahun = button.data('tahun');
                let semesterId = button.data('semester');
                let prodiId = button.data('prodi');
                let tanggal = button.data('tanggal');
                let jenisUjian = button.data('jenisUjian');


                let modal = $(this);
                modal.find('#editMatkul').val(matkulId);
                modal.find('#editPengawas').val(pengawasId);
                modal.find('#editKelas').val(kelasId);
                modal.find('#editRuangan').val(ruanganId);
                modal.find('#editJamMulai').val(jamMulai);
                modal.find('#editJamSelesai').val(jamSelesai);
                modal.find('#tahunEdit').val(tahun);
                modal.find('#editTanggal').val(tanggal);
                modal.find('#editjenisUjian').val(jenisUjian);
                modal.find('#editid').val(id);

                modal.find('#editMatkul').prop('disabled', false);

                modal.find('#editMatkul option').each(function() {
                    const matkulSemesterId = $(this).data('semester-id');
                    const matkulProdiId = $(this).data('prodi-id');

                    if (matkulSemesterId === semesterId && matkulProdiId === prodiId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }

                    if ($(this).val() == matkulId) {
                        $(this).prop('selected', true);
                    }
                });

            });

            $('#editKelas').on('change', function() {
                const selectedKelasId = $(this).val();
                const semesterId = $(this).find(':selected').data('semester-id');
                const prodiId = $(this).find(':selected').data('prodi-id');

                $('#editMatkul').val('--Matkul--').prop('disabled', false);

                $('#editMatkul option').each(function() {
                    const matkulSemesterId = $(this).data('semester-id');
                    const matkulProdiId = $(this).data('prodi-id');

                    if (matkulSemesterId === semesterId && matkulProdiId === prodiId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                $('#editSks').val('');
            });

            $('#editForm').submit(function(e) {
                e.preventDefault();

                $('input, select').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                let id = $('#editid').val();
                let matkulId = $('#editMatkul').val();
                let pengawasId = $('#editPengawas').val();
                let kelasId = $('#editKelas').val();
                let ruanganId = $('#editRuangan').val();
                let jamMulai = $('#editJamMulai').val();
                let jamSelesai = $('#editJamSelesai').val();
                let tanggal = $('#editTanggal').val();
                let jenisUjian = $('#editjenisUjian').val();
                let tahun = $('#tahunEdit').val();

                $.ajax({
                    url: '{{ route('jadwal-ujian.update', ':id') }}'.replace(':id', id),
                    type: 'PUT',
                    data: {
                        matkul_id: matkulId,
                        pengawas_id: pengawasId,
                        kelas_id: kelasId,
                        ruangan_id: ruanganId,
                        waktu_mulai: jamMulai,
                        waktu_selesai: jamSelesai,
                        tahun: tahun,
                        tanggal: tanggal,
                        jenis_ujian: jenisUjian
                    },
                    success: function(response) {
                        $('#editModal').modal('hide');
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

                            if (errors.matkul_id) {
                                $('#editMatkul').addClass('is-invalid');
                                $('#editNamaMatkulError').text(errors.matkul_id[0]);
                            }
                            if (errors.pengawas_id) {
                                $('#editPengawas').addClass('is-invalid');
                                $('#editPengawasError').text(errors.pengawas_id[0]);
                            }
                            if (errors.kelas_id) {
                                $('#editKelas').addClass('is-invalid');
                                $('#editKelasError').text(errors.kelas_id[0]);
                            }
                            if (errors.ruangan_id) {
                                $('#editRuangan').addClass('is-invalid');
                                $('#editRuanganError').text(errors.ruangan_id[0]);
                            }
                            if (errors.waktu_mulai) {
                                $('#editJamMulai').addClass('is-invalid');
                                $('#editJamMulaiError').text(errors.waktu_mulai[0]);
                            }
                            if (errors.waktu_selesai) {
                                $('#editJamSelesai').addClass('is-invalid');
                                $('#editJamSelesaiError').text(errors.waktu_selesai[0]);
                            }
                            if (errors.jenis_ujian) {
                                $('#editJenisUjian').addClass('is-invalid');
                                $('#editJenisUjianError').text(errors.jenis_ujian[0]);

                            }
                            if (errors.tanggal) {
                                $('#editTanggal').addClass('is-invalid');
                                $('#editTanggalError').text(errors.tanggal[0]);
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

            $(document).on('click', '.deleteButton', function() {
                let jadwalId = $(this).data('id');

                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Jadwal ini akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('jadwal-ujian.destroy', ':id') }}'
                                .replace(':id',
                                    jadwalId),
                            type: 'DELETE',
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: response.success,
                                    confirmButtonText: 'Oke'
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(response) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Terjadi kesalahan saat menghapus jadwal',
                                });
                            }
                        });
                    }
                });
            });
            $('.addClose, .editClose').on('click', function() {
                clearValidation('#tambahForm');
                clearValidation('#editForm');
            });

            $('#tambahModal').on('hidden.bs.modal', function() {
                clearValidation('#tambahForm');
            });

            $('#editModal').on('hidden.bs.modal', function() {
                clearValidation('#editForm');
            });

            function clearValidation(formId) {
                $(formId).find('input, select').removeClass('is-invalid');
                $(formId).find('.invalid-feedback').text('');
                $(formId)[0].reset();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const savedPengawas = localStorage.getItem('selectedPengawas') || '';
            const savedKelas = localStorage.getItem('selectedKelas') || '';
            const savedJenis = localStorage.getItem('selectedJenis') || '';

            if (savedPengawas) {
                document.getElementById('pengawasFilter').value = savedPengawas;
            }
            if (savedKelas) {
                document.getElementById('kelasFilter').value = savedKelas;
            }
            if (savedJenis) {
                document.getElementById('jenisFilter').value = savedJenis;
            }

            function applyFilters() {
                const searchQuery = $('#search').val();
                const pengawasFilter = $('#pengawasFilter').val();
                const kelasFilter = $('#kelasFilter').val();
                const jenisFilter = $('#jenisFilter').val();

                $.ajax({
                    url: '{{ route("jadwal-ujian.search") }}',
                    method: 'GET',
                    data: {
                        search: searchQuery,
                        pengawas: pengawasFilter,
                        kelas: kelasFilter,
                        jenis: jenisFilter
                    },
                    success: function(response) {
                        $('tbody').empty();

                        if (response.data.length == 0) {
                            $('tbody').append(`
                        <tr>
                            <td colspan="9" class="text-center">Jadwal belum ditambahkan</td>
                        </tr>
                    `);
                            return;
                        }

                        response.data.forEach(function(jadwal, index) {
                            $('tbody').append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td>${jadwal.matkul.nama_matkul}</td>
                            <td>${jadwal.pegawai.nama}</td>
                            <td>${jadwal.jenis_ujian === 'uts' ? 'Ujian Tengah Semester' : 'Ujian Akhir Semester'}</td>
                            <td>${jadwal.ruangan.nama}</td>
                            <td>${jadwal.kelas.nama_kelas}</td>
                            <td>${jadwal.tanggal}</td>
                            <td>${jadwal.waktu_mulai.substring(0, 5)} - ${jadwal.waktu_selesai.substring(0, 5)}</td>
                            <td>
                                <button class="btn btn-primary btn-sm editButton"
                                    data-id="${jadwal.id}"
                                    data-matkul="${jadwal.matkul.id}"
                                    data-pengawas="${jadwal.pegawai.id}"
                                    data-kelas="${jadwal.kelas_id}"
                                    data-semester="${jadwal.kelas.id_semester}"
                                    data-prodi="${jadwal.kelas.id_prodi}"
                                    data-ruangan="${jadwal.ruangan.id}"
                                    data-tahun="${jadwal.tahun}"
                                    data-jam_mulai="${jadwal.waktu_mulai}"
                                    data-jam_selesai="${jadwal.waktu_selesai}"
                                    data-tanggal="${jadwal.tanggal}"
                                    data-jenis-ujian="${jadwal.jenis_ujian}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal">
                                    <span class="mdi mdi-pencil"></span> Edit
                                </button>
                                <button class="btn btn-danger btn-sm deleteButton"
                                    data-id="${jadwal.id}">
                                    <span class="mdi mdi-delete"></span> Hapus
                                </button>
                            </td>
                        </tr>
                    `);
                        });
                    },
                    error: function() {
                        console.error('Terjadi kesalahan saat memuat data jadwal.');
                    }
                });
            }

            $('#search').on('keyup', function() {
                applyFilters();
            });

            $('#applyFilter').on('click', function() {
                const pengawas = $('#pengawasFilter').val();
                const kelas = $('#kelasFilter').val();
                const jenis = $('#jenisFilter').val();

                localStorage.setItem('selectedPengawas', pengawas);
                localStorage.setItem('selectedKelas', kelas);
                localStorage.setItem('selectedJenis', jenis);

                applyFilters();

                const dropdownToggle = document.getElementById('filterDropdown');
                const bsDropdown = bootstrap.Dropdown.getInstance(dropdownToggle);
                if (bsDropdown) bsDropdown.hide();
            });

            $('#clearSearchButton').on('click', function() {
                $('#search').val('');
                applyFilters();
            });

            applyFilters();
        });
    </script>
@endsection
