<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateLDMSRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('student')->check();
    }

    public function rules(): array
    {
        return [
            'message_content' => 'nullable|string|max:20000',
            'media_type'      => 'required|in:text,image,audio,mixed',
            'media_files'     => 'nullable|array|max:5',
            'media_files.*'   => 'file|mimes:jpg,jpeg,png,mp3,wav,webm,ogg,m4a,mp4,mov|max:20480',
        ];
    }
}
