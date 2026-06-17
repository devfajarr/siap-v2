<?php

namespace App\Http\Requests\V2\Admin\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;

class StoreMahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): bool|array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'tahun_masuk' => 'required|string|max:4',
            'nim' => 'required|numeric|unique:mahasiswas,nim',
            'nisn' => 'nullable|numeric|unique:mahasiswas,nisn',
            'nik' => 'required|numeric|unique:mahasiswas,nik',
            'email' => 'required|email|unique:mahasiswas,email',
            'alamat' => 'required|string',
            'password' => 'required|string|min:6',
            'dosen_pembimbing_id' => 'required|exists:dosens,id',
            'no_telephone' => 'required|numeric|unique:mahasiswas,no_telephone',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:255',
            'nama_ibu' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan',
            'kelas_id' => 'required|exists:kelas,id',
            'id_agama' => 'nullable|exists:feeder_agamas,id_agama',
            'id_wilayah' => 'nullable|exists:feeder_wilayahs,id_wilayah',
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_masuk.required' => 'Tahun Masuk harus diisi',
            'nama_lengkap.required' => 'Nama lengkap harus diisi',
            'nim.required' => 'NIM harus diisi',
            'nim.unique' => 'NIM sudah terdaftar',
            'nisn.required' => 'NISN harus diisi',
            'nisn.unique' => 'NISN sudah terdaftar',
            'nik.required' => 'NIK harus diisi',
            'nik.unique' => 'NIK sudah terdaftar',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'alamat.required' => 'Alamat harus diisi',
            'password.required' => 'Password harus diisi',
            'dosen_pembimbing_id.required' => 'Dosen pembimbing akademik harus diisi',
            'no_telephone.required' => 'Nomor telepone harus diisi',
            'no_telephone.unique' => 'Nomor telephone sudah tedaftar',
            'tanggal_lahir.required' => 'Tanggal lahir harus diisi',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
            'tempat_lahir.required' => 'Tempat lahir harus diisi',
            'nama_ibu.required' => 'Nama Ibu harus diisi',
            'jenis_kelamin.required' => 'Jenis kelamin harus diisi',
            'kelas_id.required' => 'Kelas harus dipilih',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid',
        ];
    }
}
