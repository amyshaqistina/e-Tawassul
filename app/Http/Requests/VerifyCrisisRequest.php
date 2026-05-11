<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VerifyCrisisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'admin_remarks'   => 'nullable|string|max:2000',
            'donation_target' => 'nullable|numeric|min:0|max:1000000',
            'impact_level'    => 'nullable|in:critical,high,medium,low',
        ];
    }
}
