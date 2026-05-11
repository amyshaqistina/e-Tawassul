<?php

namespace App\Mail;

use App\Models\CrisisReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CrisisReportSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CrisisReport $report, public string $recipientName = '')
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'e-Tawassul — Crisis Report Received',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.crisis-report-submitted',
            with: [
                'report' => $this->report,
                'name'   => $this->recipientName,
            ],
        );
    }
}
