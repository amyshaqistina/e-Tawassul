<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TwoFactorService
{
    public function generate(string $identifier, string $purpose = 'login', int $minutes = 5): string
    {
        // Invalidate any existing un-used codes for this identifier+purpose
        OtpCode::where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->update(['used' => true]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'identifier' => $identifier,
            'purpose'    => $purpose,
            'code_hash'  => Hash::make($code),
            'expires_at' => now()->addMinutes($minutes),
            'used'       => false,
        ]);

        return $code;
    }

    public function verify(string $identifier, string $code, string $purpose = 'login'): bool
    {
        $record = OtpCode::where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$record) return false;

        if (!Hash::check($code, $record->code_hash)) return false;

        $record->update(['used' => true]);
        return true;
    }
}
