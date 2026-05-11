<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected function resolveRecipient(): array
    {
        foreach (['student', 'admin', 'nok', 'lecturer'] as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                return [$guard, (string) $user->getKey()];
            }
        }
        return ['guest', '0'];
    }

    public function index()
    {
        [$type, $id] = $this->resolveRecipient();
        if ($type === 'guest') {
            return redirect()->route('login');
        }

        $notifications = NotificationLog::forRecipient($type, $id)
            ->orderByDesc('timestamp')
            ->paginate(20);

        return view('notifications.index', compact('notifications', 'type'));
    }

    public function markAsRead(int $id): JsonResponse
    {
        [$type, $rid] = $this->resolveRecipient();
        if ($type === 'guest') {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $row = NotificationLog::where('notification_id', $id)
            ->where('recipient_type', $type)
            ->where('recipient_id', $rid)
            ->first();

        if (!$row) {
            return response()->json(['error' => 'not_found'], 404);
        }

        if (is_null($row->read_at)) {
            $row->update(['read_at' => now()]);
        }

        return response()->json(['ok' => true, 'id' => $row->notification_id]);
    }

    public function unreadCount(): JsonResponse
    {
        [$type, $id] = $this->resolveRecipient();
        if ($type === 'guest') {
            return response()->json(['count' => 0, 'recent' => []]);
        }

        $count = NotificationLog::forRecipient($type, $id)->unread()->count();

        $recent = NotificationLog::forRecipient($type, $id)
            ->unread()
            ->orderByDesc('timestamp')
            ->limit(5)
            ->get(['notification_id', 'subject', 'notification_message', 'link', 'timestamp']);

        return response()->json([
            'count'  => $count,
            'recent' => $recent,
        ]);
    }
}
