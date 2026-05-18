<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\InformasiLandingPage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InformasiTambahanController extends Controller
{
    /**
     * Menampilkan halaman pengelolaan Informasi Tambahan (Kalender & Brosur).
     */
    public function index()
    {
        $kalender = InformasiLandingPage::where('tipe', 'kalender')->first();
        $brosurs = InformasiLandingPage::where('tipe', 'brosur')->latest()->paginate(10);

        return Inertia::render('Admin/InformasiTambahan/Index', [
            'kalender' => $kalender,
            'brosurs' => $brosurs,
        ]);
    }

    /**
     * Menyimpan atau memperbarui file PDF Kalender Akademik.
     */
    public function storeKalender(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:5120', // Max 5MB
        ], [
            'pdf_file.required' => 'File PDF kalender wajib diunggah.',
            'pdf_file.mimes' => 'File harus berupa dokumen PDF.',
            'pdf_file.max' => 'Ukuran file PDF maksimal 5MB.',
        ]);

        $file = $request->file('pdf_file');
        
        // Simpan ke storage/app/public/images/kalender
        // Menggunakan penamaan dengan timestamp/hash unik untuk mencegah stale cache di browser
        $filename = 'kalender_' . time() . '.pdf';
        $storedPath = $file->storeAs('images/kalender', $filename, 'public');
        $fullDbPath = 'storage/' . $storedPath;

        // Bersihkan file kalender lama di storage jika ada untuk menghemat ruang
        $oldKalender = InformasiLandingPage::where('tipe', 'kalender')->first();
        if ($oldKalender && $oldKalender->nama && str_starts_with($oldKalender->nama, 'storage/')) {
            $relativePath = str_replace('storage/', '', $oldKalender->nama);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        // Simpan / perbarui ke database secara aman (tanpa siklus delete-save yang rentan bug)
        InformasiLandingPage::updateOrCreate(
            ['tipe' => 'kalender'],
            [
                'nama' => $fullDbPath,
                'keterangan' => 'Kalender Akademik',
                'updated_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Kalender Akademik berhasil diperbarui.');
    }

    /**
     * Mengunggah brosur baru ke galeri.
     */
    public function storeBrosur(Request $request)
    {
        $request->validate([
            'gambar_brosur' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // Max 5MB
            'keterangan_brosur' => 'required|string|max:500',
        ], [
            'gambar_brosur.required' => 'Gambar brosur wajib dipilih.',
            'gambar_brosur.image' => 'File harus berupa gambar.',
            'gambar_brosur.max' => 'Ukuran gambar maksimal 5MB.',
            'keterangan_brosur.required' => 'Keterangan brosur wajib diisi.',
        ]);

        if ($request->hasFile('gambar_brosur')) {
            $image = $request->file('gambar_brosur');
            $imageName = Str::random(12) . '_' . time() . '.' . $image->getClientOriginalExtension();
            $storedPath = $image->storeAs('images/brosur', $imageName, 'public');
            $fullDbPath = 'storage/' . $storedPath;

            InformasiLandingPage::create([
                'nama' => $fullDbPath,
                'keterangan' => $request->keterangan_brosur,
                'tipe' => 'brosur',
            ]);

            return redirect()->back()->with('success', 'Brosur informasi berhasil ditambahkan.');
        }

        return redirect()->back()->with('error', 'Gagal mengunggah file gambar brosur.');
    }

    /**
     * Menghapus brosur atau informasi tambahan.
     */
    public function destroy($id)
    {
        $informasi = InformasiLandingPage::findOrFail($id);

        if ($informasi->nama) {
            if (str_starts_with($informasi->nama, 'storage/')) {
                // Hapus via disk public
                $relativePath = str_replace('storage/', '', $informasi->nama);
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            } elseif (file_exists(public_path($informasi->nama))) {
                // Fallback untuk file legacy yang langsung berada di folder public
                @unlink(public_path($informasi->nama));
            }
        }

        $informasi->delete();

        return redirect()->back()->with('success', 'Data dan file brosur berhasil dihapus.');
    }
}
