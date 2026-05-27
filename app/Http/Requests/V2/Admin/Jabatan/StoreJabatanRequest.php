<?php

namespace App\Http\Requests\V2\Admin\Jabatan;

use App\Models\Jabatan;
use Illuminate\Foundation\Http\FormRequest;

class StoreJabatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_type' => 'required|in:dosen,pegawai',
            'dosens_id' => 'required_if:user_type,dosen|nullable|exists:dosens,id',
            'pegawais_id' => 'required_if:user_type,pegawai|nullable|exists:pegawais,id',
            'nama_jabatan' => [
                'required',
                'in:bpmi,kemahasiswaan,perpustakaan,sarpras,personalia',
                function ($attribute, $value, $fail) {
                    if ($this->user_type === 'dosen' && $this->dosens_id) {
                        $exists = Jabatan::where('dosens_id', $this->dosens_id)
                            ->where('nama_jabatan', $value)
                            ->exists();
                        if ($exists) {
                            $fail('Dosen ini sudah menjabat sebagai '.strtoupper($value).'.');
                        }
                    } elseif ($this->user_type === 'pegawai' && $this->pegawais_id) {
                        $exists = Jabatan::where('pegawais_id', $this->pegawais_id)
                            ->where('nama_jabatan', $value)
                            ->exists();
                        if ($exists) {
                            $fail('Pegawai ini sudah menjabat sebagai '.strtoupper($value).'.');
                        }
                    }
                },
            ],
            'password_mode' => 'required|in:base,existing,custom',
            'password' => 'required_if:password_mode,custom|nullable|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'user_type.required' => 'Jenis pengguna wajib dipilih.',
            'dosens_id.required_if' => 'Dosen wajib dipilih.',
            'dosens_id.exists' => 'Dosen tidak valid.',
            'pegawais_id.required_if' => 'Pegawai wajib dipilih.',
            'pegawais_id.exists' => 'Pegawai tidak valid.',
            'nama_jabatan.required' => 'Jabatan struktural wajib dipilih.',
            'nama_jabatan.in' => 'Jabatan struktural tidak valid.',
            'password_mode.required' => 'Mode password wajib dipilih.',
            'password.required_if' => 'Password baru harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}
