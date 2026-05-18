<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * NotificationService
 *
 * Unified entry point for sending emails + recording a NotificationLog row.
 *
 * SAFETY: For lecturer recipients, if the env flag
 * TESTING_MODE_REDIRECT_LECTURER_EMAILS=true is set, the email is REDIRECTED
 * to TESTING_MODE_LECTURER_REDIRECT_EMAIL instead of the real lecturer's
 * @iium.edu.my address. This protects real lecturers from receiving test
 * notifications during development. Set the flag to false (or omit) in
 * production to send emails to real lecturers.
 */
class NotificationService
{
    public function send(
        string $recipientType,
        string $recipientId,
        ?string $email,
        ?Mailable $mailable,
        string $notificationType,
        string $subject,
        string $message,
        ?string $link = null,
        ?string $studentId = null,
    ): NotificationLog {
        // The address we will ACTUALLY send to (may be rewritten in testing mode)
        $finalEmail = $email;
        $wasRedirected = false;

        if ($email && $mailable) {
            // Safety redirect for lecturers in testing mode
            if (
                $recipientType === 'lecturer'
                && env('TESTING_MODE_REDIRECT_LECTURER_EMAILS', false)
            ) {
                $redirectTo = env('TESTING_MODE_LECTURER_REDIRECT_EMAIL');
                if ($redirectTo) {
                    $finalEmail = $redirectTo;
                    $wasRedirected = true;
                    Log::info('Lecturer email redirected (testing mode)', [
                        'original' => $email,
                        'redirect' => $redirectTo,
                        'lecturer_id' => $recipientId,
                    ]);
                }
            }

            try {
                Mail::to($finalEmail)->queue($mailable);
            } catch (\Throwable $e) {
                Log::warning('NotificationService: failed to queue email', [
                    'recipient_type' => $recipientType,
                    'recipient_id'   => $recipientId,
                    'email'          => $finalEmail,
                    'original_email' => $email,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        // Record audit log — we store the ORIGINAL intended recipient,
        // so the admin/audit trail always shows who the system *meant* to email.
        return NotificationLog::create([
            'recipient_type'       => $recipientType,
            'recipient_id'         => $recipientId,
            'student_id'           => $studentId,
            'notification_type'    => $notificationType,
            'subject'              => $wasRedirected
                ? "[TEST MODE → {$finalEmail}] {$subject}"
                : $subject,
            'notification_message' => $message,
            'link'                 => $link,
            'timestamp'            => now(),
        ]);
    }

    public function logOnly(
        string $recipientType,
        string $recipientId,
        string $notificationType,
        string $subject,
        string $message,
        ?string $link = null,
        ?string $studentId = null,
    ): NotificationLog {
        return $this->send(
            recipientType:    $recipientType,
            recipientId:      $recipientId,
            email:            null,
            mailable:         null,
            notificationType: $notificationType,
            subject:          $subject,
            message:          $message,
            link:             $link,
            studentId:        $studentId,
        );
    }
}
