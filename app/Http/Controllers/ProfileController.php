<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    protected $userid;
    protected $role;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->userid = Session::get('user.id');
            $this->role = Session::get('user.role');
            return $next($request);
        });
    }
    public function changeProfilePicture(Request $request)
{
    $mahasiswa = Mahasiswa::findOrFail($this->userid);

    $request->validate([
        'profile_picture' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048'
    ]);

    $filename = $mahasiswa->nim . '.' . $request->file('profile_picture')->getClientOriginalExtension();

    if ($mahasiswa->profile_picture) {
        Storage::delete('public/profile_pictures/' . $mahasiswa->profile_picture);
    }

    $path = $request->file('profile_picture')->storeAs('public/profile_pictures', $filename);

    $mahasiswa->profile_picture = $filename;
    $mahasiswa->save();

    return response()->json([
        'success'=>true,
        'message' => 'Foto profil berhasil diperbarui!',
        'profile_picture' => Storage::url('public/profile_pictures/' . $filename)
    ]);
}
}
