<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function ask(Request $request, ChatbotService $bot)
    {
        $data = $request->validate([
            'message' => 'required|string|max:1000',
            'language' => 'sometimes|string|in:en,ms,ar',
        ]);

        // Detect user role from the four Laravel guards
        [$role, $userId] = $this->detectUser();

        $result = $bot->ask(
            $data['message'],
            $data['language'] ?? 'en',
            $role,
            $userId
        );

        return response()->json($result);
    }

    private function detectUser(): array
    {
        if (auth()->guard('admin')->check()) {
            return ['admin', auth()->guard('admin')->id()];
        }
        if (auth()->guard('student')->check()) {
            return ['student', auth()->guard('student')->id()];
        }
        if (auth()->guard('nok')->check()) {
            return ['nok', auth()->guard('nok')->id()];
        }
        if (auth()->guard('lecturer')->check()) {
            return ['lecturer', auth()->guard('lecturer')->id()];
        }
        return ['public', null];
    }
}
