<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmitDeathConfirmationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('nok')->check();
    }

    public function rules(): array
    {
        return [
            'student_id'    => 'required|string|exists:students,student_id',
            'admin_comments'=> 'nullable|string|max:2000',
            'media_file'    => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'media_file.required' => 'Please attach the death certificate or supporting document.',
            'media_file.max'      => 'Document must be 10 MB or smaller.',
            'student_id.exists'   => 'The specified student record could not be found.',
        ];
    }
}
