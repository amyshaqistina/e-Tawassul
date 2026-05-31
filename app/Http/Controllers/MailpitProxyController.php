<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MailpitProxyController extends Controller
{
    public function index()
    {
        try {
            // Get messages from Mailpit API
            $response = Http::get('http://127.0.0.1:8025/api/v1/messages');
            $messages = $response->json();
        } catch (\Exception $e) {
            $messages = [];
        }

        return view('admin.mailpit-viewer', [
            'messages' => $messages,
            'status' => isset($messages) ? 'Online' : 'Offline'
        ]);
    }

    public function show($id)
    {
        try {
            $response = Http::get("http://127.0.0.1:8025/api/v1/messages/{$id}");
            $message = $response->json();
        } catch (\Exception $e) {
            $message = null;
        }

        return response()->json($message);
    }
}
