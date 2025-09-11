<?php

namespace App\Http\Controllers;

use Spatie\PdfToImage\Pdf;
use Imagick;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\InformasiLandingPage;
use Illuminate\Support\Facades\Validator;

class InformasiLandingPageController extends Controller
{

    public function index()
    {
        $kalenderAkademik = InformasiLandingPage::where('tipe', 'kalender')->first();
        $brosurs = InformasiLandingPage::where('tipe', 'brosur')->latest()->get();
        return view("pages.informasi_tambahan.index", compact("kalenderAkademik", "brosurs"));
    }

    public function storeKalender(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:2048',
        ]);
    
        $pdfFile = $request->file('pdf_file');
        $folderPath = public_path('images/kalender');
        $newFilePath = $folderPath . '/kalender.pdf';
    
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
    
        if (file_exists($newFilePath)) {
            unlink($newFilePath);
            $kalender = InformasiLandingPage::where('tipe','kalender')->first();
            $kalender->delete();
            $kalender->save();
        }
    
        $pdfFile->move($folderPath, 'kalender.pdf');
        InformasiLandingPage::create([
            'nama'=>'kalender.pdf',
            'tipe'=>'kalender',
            'keterangan'=>'kalender'
        ]);
    
        return redirect()->back()->with('success', 'File PDF berhasil diunggah!');
    }
    

    public function storeBrosur(Request  $request)
    {
        $validator = Validator::make($request->all(), [
            'gambarBrosur' => 'required|image|max:2048',
            'keteranganBrosur' => 'required|string|max:255',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        if ($request->hasFile('gambarBrosur')) {
            $image = $request->file('gambarBrosur');
            $imageName = Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('public/images/brosur', $imageName);
            $imagePath = str_replace('public/', 'storage/', $imagePath);
        }


        InformasiLandingPage::create([
            'nama' => $imagePath,
            'keterangan' => $request->keteranganBrosur,
            'tipe' => 'brosur'
        ]);

        return redirect()->back()->with('success' , 'Brosur berhasil diupload');
    }

    public function destroy(Request $request)
    {
        $informasi = InformasiLandingPage::findOrFail($request->id);
        
        $gambarPath = public_path($informasi->nama); 
        
        if (file_exists($gambarPath)) {
            unlink($gambarPath); 
        }
        
        $informasi->delete();
    
        return redirect()->back()->with('success' ,'Data dan gambar berhasil dihapus.');
    }
    
}
