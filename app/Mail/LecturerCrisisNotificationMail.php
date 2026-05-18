<?php

namespace App\Mail;

use App\Models\CrisisReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LecturerCrisisNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Lecturer-side display fields.
     *
     * IMPORTANT: these are NOT typed properties. Old queued payloads serialized
     * before these fields were added do not contain them, and PHP leaves typed
     * properties UNINITIALIZED on unserialize, which throws "must not be
     * accessed before initialization". Untyped properties default to null on
     * unserialize, which content() handles. This makes the Mailable robust
     * against stale queue payloads after schema changes.
     */
    public $lecturerName;
    public $courseName;
    public $studentMatric;
    public $crisisType;
    public $studentEmail;

    public function __construct(
        public CrisisReport $report,
        public string $studentName,
        public string $courseCode,
        $lecturerName = null,
        $courseName = null,
        $studentMatric = null,
        $crisisType = null,
        $studentEmail = null,
    ) {
        $this->lecturerName  = $lecturerName;
        $this->courseName    = $courseName;
        $this->studentMatric = $studentMatric;
        $this->crisisType    = $crisisType;
        $this->studentEmail  = $studentEmail;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notification: Student Crisis Report Verified - ' . ($this->courseCode ?? 'Course'),
        );
    }

    public function content(): Content
    {
        // CrisisReport has `date_reported`, not `incident_date`. Fall back
        // to verified_at, then to "today" if both are null.
        $incidentDate = $this->report->date_reported
            ?? $this->report->verified_at
            ?? now();

        // Resolve verified-by display name from the verifier() relation
        // (admin_verification FK -> Admin). Eager-loaded by the controller.
        $verifiedByName = 'IIUM Administration';
        $verifier = $this->report->verifier ?? null;
        if ($verifier) {
            $name = trim(($verifier->first_name ?? '') . ' ' . ($verifier->last_name ?? ''));
            $verifiedByName = $name !== ''
                ? $name
                : ($verifier->admin_name ?? $verifier->name ?? 'IIUM Administration');
        }

        $verifiedAt = $this->report->verified_at ?? now();

        $studentName = (string) ($this->studentName ?? '');
        $firstName   = $studentName !== ''
            ? explode(' ', trim($studentName))[0]
            : 'the student';

        return new Content(
            view: 'emails.lecturer_crisis_notification',
            with: [
                'lecturerName'     => $this->lecturerName ?: 'Lecturer',
                'courseCode'       => $this->courseCode ?: 'N/A',
                'courseName'       => $this->courseName,
                'studentName'      => $studentName !== '' ? $studentName : 'the student',
                'studentMatric'    => $this->studentMatric ?: 'N/A',
                'crisisType'       => $this->crisisType ?: 'N/A',
                'incidentDate'     => $incidentDate->format('d M Y'),
                'verifiedBy'       => $verifiedByName,
                'verifiedAt'       => $verifiedAt->format('d M Y, h:i A'),
                'studentFirstName' => $firstName,
                'studentEmail'     => $this->studentEmail,
                'reportId'         => $this->report->report_id ?? $this->report->id ?? 0,
            ],
        );
    }
}
