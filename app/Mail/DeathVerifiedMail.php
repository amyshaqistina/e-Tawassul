<?php

namespace App\Mail;

use App\Models\DeathConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeathVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DeathConfirmation $confirmation,
        public string $recipientName = '',
        public ?string $blockchainHash = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'e-Tawassul — Death Confirmation Verified',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.death-verified',
            with: [
                'confirmation'   => $this->confirmation,
                'name'           => $this->recipientName,
                'blockchainHash' => $this->blockchainHash,
            ],
        );
    }
}
