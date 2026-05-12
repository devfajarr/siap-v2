<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TahunAkademikController extends Controller
{
    public function index()
    {
        $tahuns = TahunAkademik::latest()->get();

        return Inertia::render('Admin/TahunAkademik/Index', [
            'tahuns' => $tahuns,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun_akademik' => [
                'required',
                'regex:/^[0-9]{4}\/[0-9]{4}$/'
            ],
            'status' => 'required|boolean',
        ], [
            'tahun_akademik.required' => 'Tahun akademik wajib diisi',
            'tahun_akademik.regex' => 'Format tahun akademik tidak valid [YYYY/YYYY]',
            'status.required' => 'Status wajib dipilih',
        ]);

        $tahun = explode('/', $request->tahun_akademik);
        $tahunPertama = (int) $tahun[0];
        $tahunKedua = (int) $tahun[1];

        if ($tahunKedua <= $tahunPertama) {
            return redirect()->back()->withErrors(['tahun_akademik' => 'Tahun kedua harus lebih besar dari tahun pertama']);
        }

        if ($request->status == 1) {
            TahunAkademik::where('status', 1)->update(['status' => 0]);
        }

        TahunAkademik::create([
            'tahun_akademik' => $request->tahun_akademik,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Tahun akademik berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tahun_akademik' => [
                'required',
                'regex:/^[0-9]{4}\/[0-9]{4}$/'
            ],
            'status' => 'required|boolean',
        ], [
            'tahun_akademik.required' => 'Tahun akademik wajib diisi.',
            'tahun_akademik.regex' => 'Format tahun akademik tidak valid [YYYY/YYYY]',
            'status.required' => 'Status wajib dipilih',
        ]);

        $tahun = explode('/', $request->tahun_akademik);
        $tahunPertama = (int) $tahun[0];
        $tahunKedua = (int) $tahun[1];

        if ($tahunKedua <= $tahunPertama) {
            return redirect()->back()->withErrors(['tahun_akademik' => 'Tahun kedua harus lebih besar dari tahun pertama']);
        }

        if ($request->status == 1) {
            TahunAkademik::where('status', 1)->update(['status' => 0]);
        }

        $tahunAkademik = TahunAkademik::findOrFail($id);
        $tahunAkademik->update([
            'tahun_akademik' => $request->tahun_akademik,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Tahun akademik berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tahun = TahunAkademik::findOrFail($id);
        $tahun->delete();

        return redirect()->back()->with('success', 'Tahun akademik berhasil dihapus.');
    }
}
