<?php

namespace App\Mail;

use App\Models\DeathConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to a lecturer when an admin verifies a death confirmation,
 * informing them that their student has passed away.
 *
 * Uses the same defensive pattern as LecturerCrisisNotificationMail:
 * untyped public properties (so stale queue payloads after schema
 * changes don't break with "must not be accessed before initialization"),
 * and content() handles missing/null values gracefully.
 */
class LecturerDeathNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $lecturerName;
    public $courseName;
    public $studentMatric;

    public function __construct(
        public DeathConfirmation $confirmation,
        public string $studentName,
        public string $courseCode,
        $lecturerName = null,
        $courseName = null,
        $studentMatric = null,
    ) {
        $this->lecturerName  = $lecturerName;
        $this->courseName    = $courseName;
        $this->studentMatric = $studentMatric;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notification: Student Bereavement - ' . ($this->courseCode ?? 'Course'),
        );
    }

    public function content(): Content
    {
        $verifiedAt = $this->confirmation->date_confirmed
            ?? $this->confirmation->updated_at
            ?? now();

        return new Content(
            view: 'emails.lecturer-death-notification',
            with: [
                'lecturerName'  => $this->lecturerName ?: 'Lecturer',
                'courseCode'    => $this->courseCode ?: 'N/A',
                'courseName'    => $this->courseName,
                'studentName'   => $this->studentName ?: 'the student',
                'studentMatric' => $this->studentMatric ?: 'N/A',
                'verifiedAt'    => $verifiedAt->format('d M Y, h:i A'),
                'confirmationId' => $this->confirmation->confirmation_id ?? 0,
            ],
        );
    }
}
