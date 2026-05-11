<?php

namespace App\Mail;

use App\Models\CrisisReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CrisisVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CrisisReport $report,
        public string $recipientName = '',
        public ?string $blockchainHash = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'e-Tawassul — Crisis Report Verified',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.crisis-verified',
            with: [
                'report'         => $this->report,
                'name'           => $this->recipientName,
                'blockchainHash' => $this->blockchainHash,
            ],
        );
    }
}
