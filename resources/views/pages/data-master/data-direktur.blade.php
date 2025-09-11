@extends('layouts.main')

@section('container')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="breadcrumb">
                <a href="/presensi/dashboard" class="breadcrumb-item">
                    <span class="mdi mdi-home"></span> Dashboard
                </a>
                <span class="breadcrumb-item" id="dataMasterBreadcrumb">Data Master</span>
                <span class="breadcrumb-item active">Direktur</span>
            </div> 
            <div class="row">
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-header bg-white">
                            <div class="p-2">
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#tambahModal">
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
                                            <th>Nama</th>
                                            <th>Status</th>
                                            <th>Email</th>
                                            <th>Nomor WhatsApp</th>
                                            <th>Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($direkturs as $direktur)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $direktur->nama }}</td>
                                                @if ($direktur->status == 1)
                                                    <td><span class="bg-success rounded"
                                                            style="width: 15px; height: 15px; display: inline-block;"></span>
                                                    </td>
                                                @else
                                                    <td>
                                                        <span class="bg-danger rounded"
                                                            style="width: 15px; height: 15px; display: inline-block;"></span>
                                                    </td>
                                                @endif
                                                <td>{{ $direktur->email }}</td>
                                                <td>{{ $direktur->no_telephone }}</td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm edit-button"
                                                        data-id="{{ $direktur->id }}"
                                                        data-dosens-id="{{ $direktur->dosens_id }}"
                                                        data-email="{{ $direktur->email }}"
                                                        data-nomor="{{ $direktur->no_telephone }}"
                                                        data-status="{{ $direktur->status }}">
                                                        <span class="mdi mdi-pencil"></span> Edit
                                                    </button>


                                                    <button class="btn btn-danger btn-sm delete-button"
                                                        data-id="{{ $direktur->id }}">
                                                        <span class="mdi mdi-delete"></span> Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" colspan="6">direktur belum ditambahkan</td>
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


    {{-- tambah --}}
    <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahModalLabel">Tambah direktur</h5>
                    <button type="button" class="btn-close addClose" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="tambahForm">
                        @csrf
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama <span style="color: red;">*</span></label>
                            <select class="form-select" id="id">
                                <option selected value="">--Nama--</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}">{{ $dosen->nama }}</option>
                                @endforeach
                            </select>
                            <div id="dosenError" class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span style="color: red;">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-sm" id="password" name="password"
                                    placeholder="Password" autocomplete="off">
                                <span class="input-group-text">
                                    <i class="fa fa-eye" id="togglePassword" style="cursor: pointer;"></i>
                                </span>
                            </div>
                            <div id="passwordError" class="invalid-feedback"></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <span class="mdi mdi-content-save"></span> Simpan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- edit --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit direktur</h5>
                    <button type="button" class="btn-close editClose" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        @csrf
                        <input type="hidden" id="direktur_id">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Direktur <span style="color: red;">*</span></label>
                            <select class="form-select" id="idEdit" name="nama">
                                <option selected value="">--Direktur--</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}">{{ $dosen->nama }}</option>
                                @endforeach
                            </select>
                            <div id="dosenErrorEdit" class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span style="color: red;">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-sm" id="passwordEdit" name="password"
                                    placeholder="Password" autocomplete="off">
                                <span class="input-group-text">
                                    <i class="fa fa-eye" id="toggleEditPassword" style="cursor: pointer;"></i>
                                </span>
                            </div>
                            <div id="passwordEdit" class="invalid-feedback"></div>
                        </div>
                        <label class="form-label">Status <span style="color: red;">*</span></label><br>
                        <div class="d-flex flex-wrap">
                            <div class="form-group me-3">
                                <div class="form-check form-check-primary">
                                    <label class="form-check-label" for="status_aktifEdit">
                                        <input class="form-check-input" type="radio" name="status"
                                            id="status_aktifEdit" value="1" required>
                                        Aktif</label>
                                </div>
                            </div>
                            <div class="form-group me-3">
                                <div class="form-check form-check-primary">
                                    <label class="form-check-label" for="status_non_aktifEdit">
                                        <input class="form-check-input" type="radio" name="status"
                                            id="status_non_aktifEdit" value="0" required>
                                        Non-Aktif</label>
                                </div>
                            </div>
                        </div>
                        <div id="statusError" class="invalid-feedback"></div>

                        <button type="submit" class="btn btn-primary btn-sm">
                            <span class="mdi mdi-content-save"></span> Simpan
                        </button>
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

            $('#togglePassword').on('click', function() {
                let passwordInput = $('#password');
                let icon = $(this);
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $('#tambahForm').submit(function(e) {
                e.preventDefault();
                $('input, select, textarea').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                let id = $('#id').val();
                let password = $('#password').val();

                $('#dosenError, #emailError, #passwordError')
                    .text('').removeClass('is-invalid');

                $.ajax({
                    url: '{{ route('data-direktur.store') }}',
                    method: 'POST',
                    data: {
                        dosens_id: id,
                        password: password,
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
                            if (errors.dosens_id) {
                                $('#id').addClass('is-invalid');
                                $('#dosenError').text(errors.dosens_id[0]);
                            }
                            if (errors.password) {
                                $('#password').addClass('is-invalid');
                                $('#passwordError').text(errors.password[0]);
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


            $('.edit-button').on('click', function() {
                var id = $(this).data('id');
                var dosens_id = $(this).data('dosensId');
                var status = $(this).data('status');


                $('#direktur_id').val(id);
                $('#idEdit').val(dosens_id).trigger('change'); 
                $('#statusEdit').val(status);

                if (status == 1) {
                    $('#status_aktifEdit').prop('checked', true);
                } else {
                    $('#status_non_aktifEdit').prop('checked', true);
                }

                $('#editModal').modal('show');
            });

            $('#editForm').submit(function(e) {
                e.preventDefault();
                let id = $('#direktur_id').val();
                let dosens_id = $('#idEdit').val();
                let status = $('input[name="status"]:checked').val();
                let password = $('#passwordEdit').val();               

                $.ajax({
                    url: '{{ route('data-direktur.update', ':id') }}'.replace(':id', id),
                    method: 'PUT',
                    data: {
                        dosens_id: dosens_id,
                        status: status,
                        password : password,
                    },
                    success: function(response) {
                        $('#editModal').modal('hide');
                        $('#editForm')[0].reset();
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

                            if (errors.dosens_id) {
                                $('#idEdit').addClass('is-invalid');
                                $('#dosenErrorEdit').text(errors.dosens_id[0]);
                            }
                            if (errors.password) {
                                $('#passwordEdit').addClass('is-invalid');
                                $('#passwordErrorEdit').text(errors.password[0]);
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


            $('.delete-button').on('click', function() {
                const deleteId = $(this).data('id');
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Apakah Anda yakin ingin menghapus Direktur ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('data-direktur.destroy', ':id') }}'.replace(
                                ':id',
                                deleteId),
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
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
                                    text: 'Terjadi kesalahan. Silakan coba lagi.',
                                });
                            }
                        });
                    }
                });
            });

            $('#toggleEditPassword').on('click', function() {
                let passwordInput = $('#passwordEdit');
                let icon = $(this);
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
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
    </script>
@endsection
