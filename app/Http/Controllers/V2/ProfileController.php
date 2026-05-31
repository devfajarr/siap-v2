<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile picture.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if (! Auth::guard('mahasiswa')->check()) {
            return back()->with('error', 'Hanya mahasiswa yang dapat mengubah foto profil.');
        }

        $user = Auth::guard('mahasiswa')->user();

        $filename = $user->nim.'_'.time().'.'.$request->file('profile_picture')->getClientOriginalExtension();

        if ($user->profile_picture) {
            Storage::delete('public/profile_pictures/'.$user->profile_picture);
        }

        $request->file('profile_picture')->storeAs('public/profile_pictures', $filename);

        $user->profile_picture = $filename;
        $user->save();

        return back()->with('success', 'Foto profil berhasil diperbarui!');
    }
}
