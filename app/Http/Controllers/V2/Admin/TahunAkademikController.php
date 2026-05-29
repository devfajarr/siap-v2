<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Admin\TahunAkademik\StoreTahunAkademikRequest;
use App\Http\Requests\V2\Admin\TahunAkademik\UpdateTahunAkademikRequest;
use App\Models\TahunAkademik;
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

    public function store(StoreTahunAkademikRequest $request)
    {
        if ($request->status == 1) {
            TahunAkademik::where('status', 1)->update(['status' => 0]);
        }

        TahunAkademik::create([
            'tahun_akademik' => $request->tahun_akademik,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Tahun akademik berhasil ditambahkan.');
    }

    public function update(UpdateTahunAkademikRequest $request, $id)
    {
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
