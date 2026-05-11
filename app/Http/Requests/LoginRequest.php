<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'role'       => 'required|in:student,admin,nok,lecturer',
            'identifier' => 'required|string|max:191',
            'password'   => 'required|string|min:4|max:191',
            'remember'   => 'nullable|boolean',
        ];
    }
}
