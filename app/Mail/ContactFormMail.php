<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public string $messageBody,
        public string $submittedAt,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo mensaje de contacto - Prefabricados Alesa',
            replyTo: [new Address($this->email, $this->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-form',
            with: [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'messageBody' => $this->messageBody,
                'submittedAt' => $this->submittedAt,
                'ipAddress' => $this->ipAddress,
                'userAgent' => $this->userAgent,
            ],
        );
    }
}
