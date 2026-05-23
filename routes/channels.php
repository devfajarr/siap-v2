<?php

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
