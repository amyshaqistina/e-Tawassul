<?php

namespace App\Mail;

use App\Models\CrisisReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CrisisRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CrisisReport $report,
        public string $recipientName = '',
        public ?string $reason = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'e-Tawassul — Crisis Report Update',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.crisis-rejected',
            with: [
                'report' => $this->report,
                'name'   => $this->recipientName,
                'reason' => $this->reason,
            ],
        );
    }
}
