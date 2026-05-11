<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VerifyHashRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/i',
        ];
    }

    public function messages(): array
    {
        return [
            'hash.size'  => 'A valid SHA-256 hash must be exactly 64 hexadecimal characters.',
            'hash.regex' => 'The hash must contain only hexadecimal characters (0-9, a-f).',
        ];
    }
}
