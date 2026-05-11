<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VerifyDeathRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'decision'      => 'required|in:verified,rejected',
            'admin_comments'=> 'nullable|string|max:2000',
        ];
    }
}
