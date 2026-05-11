<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DonateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public action
    }

    public function rules(): array
    {
        return [
            'donor_name'      => 'required|string|max:150',
            'donor_email'     => 'required|email|max:191',
            'donation_amount' => 'required|numeric|min:1|max:1000000',
            'payment_method'  => 'required|in:FPX,credit_card,wallet',
            'support_message' => 'nullable|string|max:1000',
            'anonymous'       => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'donation_amount.min' => 'The minimum donation amount is RM 1.',
        ];
    }
}
