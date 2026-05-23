<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{jadwalId}', function ($user, $jadwalId) {
    if (auth()->check() || $user !== null) {
        return [
            'id' => $user->id,
            'nama' => $user->nama,
            'role' => Session::get('user.role') ?? $user->role ?? 'Guest',
        ];
    }

    return false;
});

Broadcast::channel('guidance.{studentId}', function ($user, $studentId) {
    $role = Session::get('user.role');

    if (! $role) {
        if ($user instanceof Mahasiswa) {
            $role = 'mahasiswa';
        } elseif ($user instanceof Dosen) {
            $role = 'dosen';
        }
    }

    if ($role === 'mahasiswa') {
        if ((int) $user->id === (int) $studentId) {
            return [
                'id' => $user->id,
                'nama' => $user->nama_lengkap,
                'role' => 'mahasiswa',
            ];
        }
    }

    if ($role === 'dosen') {
        $student = Mahasiswa::find($studentId);
        if ($student && (int) $student->dosen_pembimbing_id === (int) $user->id) {
            return [
                'id' => $user->id,
                'nama' => $user->nama,
                'role' => 'dosen',
            ];
        }
    }

    return false;
});
