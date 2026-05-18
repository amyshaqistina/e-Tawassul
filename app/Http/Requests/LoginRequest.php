<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $role = $this->input('role', 'student');

        return [
            'role'       => 'required|in:student,admin,nok,lecturer',
            'identifier' => 'required|string|max:191',
            // NOK uses passwordless OTP flow. Password is only required
            // for student/admin/lecturer roles.
            'password'   => $role === 'nok' ? 'nullable|string|max:191' : 'required|string|min:4|max:191',
            'delivery'   => 'nullable|in:email,sms',
            'remember'   => 'nullable|boolean',
        ];
    }
}
