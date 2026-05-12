<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Http\Requests\V2\Admin\Ruangan\StoreRuanganRequest;
use App\Http\Requests\V2\Admin\Ruangan\UpdateRuanganRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RuanganController extends Controller
{
    public function index()
    {
        $ruangans = Ruangan::latest()->get();

        return Inertia::render('Admin/DataRuangan/Index', [
            'ruangans' => $ruangans,
        ]);
    }

    public function store(StoreRuanganRequest $request)
    {
        Ruangan::create($request->validated());

        return redirect()->back()->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function update(UpdateRuanganRequest $request, $id)
    {
        $ruangan = Ruangan::findOrFail($id);

        $ruangan->update($request->validated());

        return redirect()->back()->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $ruangan = Ruangan::findOrFail($id);
        $ruangan->delete();

        return redirect()->back()->with('success', 'Ruangan berhasil dihapus.');
    }
}
