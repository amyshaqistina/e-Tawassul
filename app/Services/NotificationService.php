<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * NotificationService
 *
 * Unified entrypoint for sending an email AND persisting a notification log
 * record for in-app display.
 *
 * Usage:
 *   app(NotificationService::class)->send(
 *       recipientType: 'student',
 *       recipientId:   $student->student_id,
 *       email:         $student->email,
 *       mailable:      new CrisisVerifiedMail($report, $student->first_name),
 *       notificationType: 'crisis_verified',
 *       subject:       'Your crisis report has been verified',
 *       message:       "Your crisis report #{$report->report_id} has been verified.",
 *       link:          route('student.crisis.show', $report->crisis_id),
 *       studentId:     $student->student_id,
 *   );
 */
class NotificationService
{
    /**
     * Queue an email and create a NotificationLog row.
     */
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
        // 1) Try to queue the email (graceful failure — never blocks the workflow)
        if ($email && $mailable) {
            try {
                Mail::to($email)->queue($mailable);
            } catch (\Throwable $e) {
                Log::warning('NotificationService: failed to queue email', [
                    'recipient_type' => $recipientType,
                    'recipient_id'   => $recipientId,
                    'email'          => $email,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        // 2) Persist the notification log for the in-app bell
        return NotificationLog::create([
            'recipient_type'       => $recipientType,
            'recipient_id'         => $recipientId,
            'student_id'           => $studentId,
            'notification_type'    => $notificationType,
            'subject'              => $subject,
            'notification_message' => $message,
            'link'                 => $link,
            'timestamp'            => now(),
        ]);
    }

    /**
     * Convenience helper — log only (no email).
     */
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
            recipientType: $recipientType,
            recipientId:   $recipientId,
            email:         null,
            mailable:      null,
            notificationType: $notificationType,
            subject:       $subject,
            message:       $message,
            link:          $link,
            studentId:     $studentId,
        );
    }
}
