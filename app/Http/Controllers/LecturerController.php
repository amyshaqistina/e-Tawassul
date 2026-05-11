<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Auth;

class LecturerController extends Controller
{
    public function dashboard()
    {
        /** @var \App\Models\Lecturer $lecturer */
        $lecturer = Auth::guard('lecturer')->user();

        $notifications = NotificationLog::forRecipient('lecturer', (string) $lecturer->lecturer_id)
            ->orderByDesc('timestamp')
            ->paginate(20);

        $unreadCount = NotificationLog::forRecipient('lecturer', (string) $lecturer->lecturer_id)
            ->unread()->count();

        return view('lecturer.dashboard', [
            'lecturer'      => $lecturer,
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }
}
