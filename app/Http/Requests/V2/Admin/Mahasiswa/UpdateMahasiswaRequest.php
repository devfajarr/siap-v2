<?php

namespace App\Http\Requests\V2\Admin\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->id;

        return [
            'nama_lengkap' => 'required|string|max:255',
            'tahun_masuk' => 'required|string|max:4',
            'nim' => 'required|numeric|unique:mahasiswas,nim,'.$id,
            'nisn' => 'nullable|numeric|unique:mahasiswas,nisn,'.$id,
            'nik' => 'required|numeric|unique:mahasiswas,nik,'.$id,
            'email' => 'required|email|unique:mahasiswas,email,'.$id,
            'alamat' => 'required|string',
            'password' => 'nullable|string|min:6',
            'dosen_pembimbing_id' => 'required|exists:dosens,id',
            'no_telephone' => 'required|numeric|unique:mahasiswas,no_telephone,'.$id,
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:255',
            'nama_ibu' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan',
            'kelas_id' => 'required|exists:kelas,id',
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_masuk.required' => 'Tahun masuk harus diisi',
            'nama_lengkap.required' => 'Nama lengkap harus diisi',
            'nim.required' => 'NIM harus diisi',
            'nim.unique' => 'NIM sudah terdaftar',
            'nim.numeric' => 'NIM harus berupa angka',
            'nisn.required' => 'NISN harus diisi',
            'nisn.unique' => 'NISN sudah terdaftar',
            'nisn.numeric' => 'NISN harus berupa angka',
            'nik.required' => 'NIK harus diisi',
            'nik.unique' => 'NIK sudah terdaftar',
            'nik.numeric' => 'NIK harus berupa angka',
            'email.required' => 'Email harus diisi',
            'dosen_pembimbing_id.required' => 'Dosen pembimbing akademik harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'alamat.required' => 'Alamat harus diisi',
            'no_telephone.required' => 'Nomor telepon harus diisi',
            'no_telephone.unique' => 'Nomor telepon sudah terdaftar',
            'no_telephone.numeric' => 'Nomor telepon harus berupa angka',
            'tanggal_lahir.required' => 'Tanggal lahir harus diisi',
            'tempat_lahir.required' => 'Tempat lahir harus diisi',
            'nama_ibu.required' => 'Nama Ibu harus diisi',
            'jenis_kelamin.required' => 'Jenis kelamin harus dipilih',
            'kelas_id.required' => 'Kelas harus dipilih',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid',
        ];
    }
}
