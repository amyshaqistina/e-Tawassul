<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RejectCrisisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'admin_remarks' => 'required|string|min:10|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'admin_remarks.required' => 'Please provide a reason for rejecting this report.',
            'admin_remarks.min'      => 'Rejection reason must be at least 10 characters.',
        ];
    }
}
