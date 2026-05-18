<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * SmsDemoService
 *
 * Demo-mode "SMS" provider. Writes the message to storage/logs/sms.log
 * and also exposes the latest code via session flash so the user can see
 * it in the OTP entry page (for FYP demo purposes only).
 *
 * To swap to a real provider (Twilio, Vonage, etc.) replace the body of
 * send() with the provider's SDK call. Nothing else in the auth flow
 * needs to change — the caller signature stays the same.
 */
class SmsDemoService
{
    /**
     * "Send" an SMS. In demo mode this just logs.
     *
     * @param  string  $phone   E.164-ish phone number (e.g. +60123334444)
     * @param  string  $body    Full message body
     * @return void
     */
    public function send(string $phone, string $body): void
    {
        // Dedicated sms.log channel so the demo SMS feed is separate from
        // regular app logs and easy to tail (e.g. `tail -f storage/logs/sms.log`).
        Log::channel('sms')->info('SMS dispatched (DEMO MODE)', [
            'to'   => $phone,
            'body' => $body,
        ]);
    }
}
