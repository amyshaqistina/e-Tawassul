<?php

namespace App\Mail;

use App\Models\Ldms;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LDMSReleasedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Ldms $ldms, public string $recipientName = '')
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'e-Tawassul — A Message Has Been Released for You',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ldms-released',
            with: [
                'ldms' => $this->ldms,
                'name' => $this->recipientName,
            ],
        );
    }
}
