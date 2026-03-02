<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Les données du formulaire de contact.
     */
    public array $data;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     * 
     * IMPORTANT pour OVH: le FROM doit être une adresse du domaine configuré.
     * Le visiteur est en Reply-To uniquement (jamais en FROM).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'contact@elsayara.com'),
                config('mail.from.name', 'ElSayara')
            ),
            subject: '[Contact ElSayara] ' . $this->data['subject'],
            replyTo: [
                new Address($this->data['email'], $this->data['name']),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
            with: [
                'contactName'  => $this->data['name'],
                'contactEmail' => $this->data['email'],
                'contactPhone' => $this->data['phone'],
                'subject'      => $this->data['subject'],
                'body'         => $this->data['body'],
                'ip'           => $this->data['ip'] ?? null,
                'userAgent'    => $this->data['user_agent'] ?? null,
                'sentAt'       => $this->data['sent_at'] ?? now()->format('d/m/Y à H:i'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
