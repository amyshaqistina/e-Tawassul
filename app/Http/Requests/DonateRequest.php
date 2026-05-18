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
            // Expanded set: bank_transfer + duitnow_qr added for the
            // direct-transfer flow. FPX/card/wallet kept for the demo
            // gateway flow.
            'payment_method'  => 'required|in:FPX,credit_card,wallet,bank_transfer,duitnow_qr',
            'support_message' => 'nullable|string|max:1000',
            'anonymous'       => 'nullable|boolean',
            // Optional confirmation reference number the donor types in
            // after making the actual transfer. Used purely for audit/
            // reconciliation — admin can match this to the bank statement.
            'transfer_reference' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'donation_amount.min' => 'The minimum donation amount is RM 1.',
        ];
    }
}
