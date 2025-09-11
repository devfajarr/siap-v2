@extends('layouts.main')

@section('container')
    <style>
        td img {
            width: 100%;
            /* Menyesuaikan gambar agar pas dengan kolom */
            height: auto;
            /* Menjaga proporsi gambar */
        }
    </style>
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="breadcrumb">
                <a href="/presensi/dashboard" class="breadcrumb-item">
                    <span class="mdi mdi-home"></span> Dashboard
                </a>
                <span class="breadcrumb-item" id="dataMasterBreadcrumb">Informasi Tambahan</span>
            </div>
            <div class="row">
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Kalender Akademik</h4>
                            <div class="p-2">
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#tambahKalender">
                                    <span class="mdi mdi-plus"></span> Tambah
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="pdf-container" style="max-width: 100%; overflow: hidden;">
                                @if (isset($kalenderAkademik))
                                    <!-- Pratinjau PDF menggunakan iframe -->
                                    <a href="{{ asset('images/kalender/kalender.pdf') }}" target="_blank">
                                        <iframe src="{{ asset('images/kalender/kalender.pdf') }}" width="100%"
                                            height="600" frameborder="0"></iframe>
                                    </a>
                                @else
                                    <div class="text-center">Tidak ada data</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Brosur</h4>
                            <div class="p-2">
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#tambahBrosurModal">
                                    <span class="mdi mdi-plus"></span> Tambah
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Pratinjau</th>
                                            <th>Keterangan</th>
                                            <th>Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($brosurs as $brosur)
                                            <tr>
                                                <td>
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td><a href="{{ asset($brosur->nama) }}" target="_blank"><img
                                                            src="{{ asset($brosur->nama) }}" alt="Brosur"></a></td>
                                                <td>{{ $brosur->keterangan }}</td>
                                                <td>
                                                    <form action="{{ route('informasi-tambahan-delete') }}" method="POST" id="deleteForm_{{ $brosur->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="id" value="{{ $brosur->id }}">
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $brosur->id }})">
                                                            <i class="mdi mdi-delete"> </i> Hapus
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    Belum ada brosur
                                                </td>
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

    <div class="modal fade" id="tambahKalender" tabindex="-1" aria-labelledby="tambahKalenderLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-white btn-sm text-dark">
                    <h5 class="modal-title" id="tambahKalenderLabel">Upload File PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <p class="text-danger"><strong>Jika ada jadwal yang lama maka akan digantikan dengan yang
                                baru.</strong></p>
                        <div class="mb-3">
                            <label for="pdfUpload" class="form-label">Pilih File PDF</label>
                            <input type="file" class="form-control" id="pdfUpload" accept="application/pdf" required>
                            <small class="text-muted">Format: PDF | Maks: 2MB</small>
                            <div class="mt-2 text-success fw-bold d-none" id="fileNamePreview"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="uploadBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="loadingSpinner"></span>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <div class="modal fade" id="tambahBrosurModal" tabindex="-1" aria-labelledby="tambahBrosurModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahBrosurModalLabel">Tambah Brosur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadBrosurForm" action="#" method="POST" enctype="multipart/form-data">
                        <!-- Input Gambar -->
                        <div class="mb-3">
                            <label for="gambarBrosur" class="form-label">Pilih Gambar</label>
                            <input type="file" class="form-control" id="gambarBrosur" name="gambarBrosur"
                                accept="image/*" required>
                            <div id="fileNamePreviewBrosur" class="mt-2 d-none"></div>
                        </div>
                        <div class="mb-3">
                            <label for="keteranganBrosur" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keteranganBrosur" name="keteranganBrosur" rows="4"
                                placeholder="Masukkan keterangan" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary btn-sm" id="uploadBrosurBtn">Simpan</button>
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

            $('#pdfUpload').change(function() {
                const file = this.files[0];
                if (file) {
                    if (file.type !== 'application/pdf') {
                        alert('Hanya file PDF yang diperbolehkan!');
                        $(this).val('');
                        $('#fileNamePreview').addClass('d-none');
                    } else if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file maksimal 2MB!');
                        $(this).val('');
                        $('#fileNamePreview').addClass('d-none');
                    } else {
                        $('#fileNamePreview').text('File: ' + file.name).removeClass('d-none');
                    }
                } else {
                    $('#fileNamePreview').addClass('d-none');
                }
            });

            $('#uploadForm').submit(function(e) {
                e.preventDefault();

                const file = $('#pdfUpload')[0].files[0];
                if (!file) {
                    alert('Silakan pilih file terlebih dahulu!');
                    return false;
                }

                if (file.type !== 'application/pdf') {
                    alert('Hanya file PDF yang diperbolehkan!');
                    return false;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB!');
                    return false;
                }

                let formData = new FormData();
                formData.append('pdf_file', file);

                $('#loadingSpinner').removeClass('d-none');
                $('#uploadBtn').prop('disabled', true);

                $.ajax({
                    url: '{{ route('upload.kalender') }}',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#tambahKalender').modal('hide');
                        $('#uploadForm')[0].reset();
                        $('#fileNamePreview').addClass('d-none');

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            const errors = response.responseJSON.errors;
                            if (errors.pdf_file) {
                                $('#pdfUpload').addClass('is-invalid');
                                if ($('#pdfError').length) {
                                    $('#pdfError').text(errors.pdf_file[0]);
                                }
                                alert(errors.pdf_file[0]);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan. Silakan coba lagi.',
                            });
                        }
                    },
                    complete: function() {
                        $('#loadingSpinner').addClass('d-none');
                        $('#uploadBtn').prop('disabled', false);
                    }
                });
            });

            $('#gambarBrosur').change(function() {
                const file = this.files[0];
                if (file) {
                    if (!file.type.startsWith('image/')) {
                        alert('Hanya file gambar yang diperbolehkan!');
                        $(this).val('');
                        $('#fileNamePreviewBrosur').addClass('d-none');
                    } else if (file.size > 2 * 1024 * 1024) { // Maksimal 2MB
                        alert('Ukuran file maksimal 2MB!');
                        $(this).val('');
                        $('#fileNamePreviewBrosur').addClass('d-none');
                    } else {
                        $('#fileNamePreviewBrosur').text('File: ' + file.name).removeClass('d-none');
                    }
                } else {
                    $('#fileNamePreviewBrosur').addClass('d-none');
                }
            });

            $('#uploadBrosurBtn').click(function(e) {
                e.preventDefault();

                const file = $('#gambarBrosur')[0].files[0];
                if (!file) {
                    alert('Silakan pilih file gambar terlebih dahulu!');
                    return false;
                }

                if (!file.type.startsWith('image/')) {
                    alert('Hanya file gambar yang diperbolehkan!');
                    return false;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB!');
                    return false;
                }

                let formData = new FormData();
                formData.append('gambarBrosur', file);
                formData.append('keteranganBrosur', $('#keteranganBrosur').val());

                $('#loadingSpinnerBrosur').removeClass('d-none');
                $('#uploadBrosurBtn').prop('disabled', true);

                $.ajax({
                    url: '{{ route('upload.brosur') }}',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#tambahBrosurModal').modal('hide');
                        $('#uploadBrosurForm')[0].reset();
                        $('#fileNamePreviewBrosur').addClass('d-none');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
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
                            text: 'Terjadi kesalahan. Silakan coba lagi.',
                        });
                    },
                    complete: function() {
                        $('#loadingSpinnerBrosur').addClass('d-none');
                        $('#uploadBrosurBtn').prop('disabled', false);
                    }
                });
            });
        });
    </script>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '{{ session('success') }}',
                showConfirmButton: true,
            });
        </script>
    @endif
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data dan gambar akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm_' + id).submit();
                }
            });
        }
    </script>
@endsection
