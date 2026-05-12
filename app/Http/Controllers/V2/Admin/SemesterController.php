<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Semester;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SemesterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $semesters = Semester::orderBy('semester', 'asc')->get();
        
        $ganjilActive = Semester::where('status', 1)
            ->whereRaw('semester % 2 = 1')
            ->exists();

        $genapActive = Semester::where('status', 1)
            ->whereRaw('semester % 2 = 0')
            ->exists();

        return Inertia::render('Admin/DataSemester/Index', [
            'semesters' => $semesters,
            'ganjilActive' => $ganjilActive,
            'genapActive' => $genapActive,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'semester' => 'required|numeric|max:99|unique:semesters,semester,NULL,id,deleted_at,NULL'
        ], [
            "semester.required" => "Semester harus diisi",
            "semester.numeric" => "Semester harus berupa angka",
            "semester.max" => "Maksimal 2 digit",
            "semester.unique" => "Semester ini sudah ada"
        ]);

        $activeSemester = Semester::where('status', 1)->first();
        $newSemesterIsEven = $validated['semester'] % 2 == 0;
        $status = 0;

        if ($activeSemester) {
            $activeSemesterIsEven = $activeSemester->semester % 2 == 0;

            // If parity matches current active group, make it active too
            if ($newSemesterIsEven == $activeSemesterIsEven) {
                $status = 1;
            }
        } else {
            // If no active semester, make the first one active
            $status = 1;
        }

        Semester::create([
            'semester' => $validated['semester'],
            'status' => $status
        ]);

        return redirect()->back()->with('success', 'Semester berhasil ditambahkan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $semester = Semester::findOrFail($id);
        
        // Cleanup related records in Kelas
        Kelas::where('id_semester', $semester->id)->delete();
        
        $semester->delete();

        return redirect()->back()->with('success', 'Semester berhasil dihapus.');
    }

    /**
     * Toggle status based on parity (Ganjil/Genap)
     */
    public function gantiStatus(Request $request)
    {
        $request->validate([
            'type' => 'required|in:genap,ganjil',
        ]);

        $type = $request->type;

        try {
            // Deactivate all
            Semester::query()->update(['status' => 0]);

            if ($type == 'genap') {
                $countGenap = Semester::whereRaw('semester % 2 = 0')->count();
                if ($countGenap > 0) {
                    Semester::whereRaw('semester % 2 = 0')->update(['status' => 1]);
                    return redirect()->back()->with('success', 'Semester Genap berhasil diaktifkan.');
                } else {
                    // Fallback to ganjil if no genap exists
                    Semester::whereRaw('semester % 2 != 0')->update(['status' => 1]);
                    return redirect()->back()->with('error', 'Tidak ada semester genap yang tersedia, semester ganjil tetap diaktifkan.');
                }
            } else {
                Semester::whereRaw('semester % 2 != 0')->update(['status' => 1]);
                return redirect()->back()->with('success', 'Semester Ganjil berhasil diaktifkan.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }
}
