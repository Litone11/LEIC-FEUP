<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $user = $this->user();
        $tab = request('tab', 'unread') === 'read' ? 'read' : 'unread';

        $query = $user->notifications()->orderByDesc('created_at');
        $notifications = ($tab === 'unread')
            ? $query->where('is_read', false)->get()
            : $query->where('is_read', true)->get();

        return view('pages.notifications', [
            'user'           => $user,
            'notifications'  => $notifications,
            'tab'            => $tab,
            'counts'         => [
                'unread' => $user->notifications()->where('is_read', false)->count(),
                'read'   => $user->notifications()->where('is_read', true)->count(),
            ],
        ]);
    }

    public function markAsRead(Notification $notification)
    {
        $this->authorize('update', $notification);

        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notificação marcada como lida.');
    }

    public function destroy(Notification $notification)
    {
        $this->authorize('delete', $notification);

        $notification->delete();

        return back()->with('success', 'Notificação eliminada.');
    }

}
