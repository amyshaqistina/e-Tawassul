<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmitCrisisReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('student')->check();
    }

    public function rules(): array
    {
        return [
            'crisis_type'         => 'required|in:death,accident,illness,natural_disaster,family_emergency',
            'crisis_description'  => 'required|string|min:30|max:5000',
            'crisis_details'      => 'nullable|string|max:5000',
            'impact_level'        => 'required|in:critical,high,medium,low',
            'location'            => 'nullable|string|max:255',
            'donation_target'     => 'nullable|numeric|min:0|max:1000000',
            'report_description'  => 'required|string|min:30|max:5000',
            'supporting_evidence' => 'nullable|array|max:5',
            'supporting_evidence.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'crisis_description.min' => 'Please provide at least 30 characters describing the crisis.',
            'report_description.min' => 'Please provide at least 30 characters explaining the situation.',
            'supporting_evidence.*.max' => 'Each file must be 5 MB or smaller.',
        ];
    }
}
