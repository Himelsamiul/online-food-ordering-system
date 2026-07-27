<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Support\AdminNotifications;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Stamp "I have seen these" on the signed-in admin. Everything created
     * after this moment shows up as new next time.
     */
    public function markAllRead(Request $request)
    {
        $admin = $request->user();
        $admin->forceFill(['notifications_seen_at' => now()])->save();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('success', 'Notifications marked as read.');
    }

    /**
     * Same payload the header renders, for the poller that keeps the badge
     * current without a page reload.
     */
    public function feed(Request $request)
    {
        $data = AdminNotifications::for($request->user());

        return response()->json([
            'total'  => $data['total'],
            'unread' => $data['unread'],
            'items'  => $data['items']->map(fn ($item) => [
                'icon'  => $item['icon'],
                'tone'  => $item['tone'],
                'title' => $item['title'],
                'body'  => $item['body'],
                'url'   => $item['url'],
                'time'  => $item['time']?->diffForHumans(),
                'isNew' => $item['is_new'],
            ])->values(),
        ]);
    }
}
