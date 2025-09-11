@extends('layouts.main')

@section('container')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="breadcrumb">
                <a href="/presensi/dashboard" class="breadcrumb-item">
                    <span class="mdi mdi-home"></span> Dashboard
                </a>
                <span class="breadcrumb-item" id="dataMasterBreadcrumb">Data Pengajuan Edit Presensi</span>
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
                                            <th>Nama Dosen</th>
                                            <th>Mata Kuliah</th>
                                            <th>Pertemuan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pengajuans as $pengajuan)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $pengajuan->jadwal->dosen->nama }}</td>
                                                <td>{{ $pengajuan->jadwal->matkul->nama_matkul }}</td>
                                                <td>Pertemuan {{ $pengajuan->pertemuan }}</td>
                                                <td>
                                                    <form action="{{ route('request.edit.verify') }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" value="{{ $pengajuan->id }}" name="id">
                                                        <button class="btn btn-primary btn-sm"><i class="mdi mdi-check"></i>
                                                            Verifiakasi</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" colspan="6">Belum ada pengajuan edit presensi
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $pengajuans->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <script>
            $(document).ready(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{{ session('success') }}',
                    showConfirmButton: true,
                }).then(() => {
                    location.reload();
                });
            });
        </script>
    @endif
@endsection
