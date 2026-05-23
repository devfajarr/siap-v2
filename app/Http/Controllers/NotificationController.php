<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Resolve the currently authenticated user across all guards.
     */
    protected function resolveUser(): mixed
    {
        foreach (['admin', 'dosen', 'mahasiswa', 'kaprodi', 'direktur', 'wakil_direktur'] as $guard) {
            if (auth()->guard($guard)->check()) {
                return auth()->guard($guard)->user();
            }
        }

        return null;
    }

    public function getNotifications()
    {
        $user = $this->resolveUser();

        if (! $user) {
            return response()->json(['notifications' => [], 'unread_count' => 0], 401);
        }

        $notifications = $user->unreadNotifications()->latest()->get();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markNotificationsAsRead(Request $request)
    {
        $user = $this->resolveUser();

        if (! $user) {
            return response()->json(['success' => false], 401);
        }

        if ($request->has('notification_id')) {
            $notification = $user->unreadNotifications
                ->where('id', $request->input('notification_id'))
                ->first();

            if ($notification) {
                $notification->markAsRead();
            }
        } else {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json([
            'success' => true,
            'total_unread' => $user->unreadNotifications()->count(),
        ]);
    }
}
