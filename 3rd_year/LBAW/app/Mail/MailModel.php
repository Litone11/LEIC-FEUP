<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address; 
use Illuminate\Queue\SerializesModels;

class MailModel extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new messag
     * e instance.
     */
    public $mailData;
    public function __construct(array $mailData)
    {
             $this->mailData = $mailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')),
            subject: 'Mail Model',
        );

    }
    public function addMembersEnvelope():Envelope{
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')),
            subject: 'Convite para projeto',
        );
    }

    /**
     * Get the message content definition.
     */
    public function addMembersContent():Content{
        return new Content(
            view: 'emails.project_invitation',
            with:  ['mailData' => $this->mailData],
        );
    }
    public function content(): Content
    {
        return new Content(
            view: 'emails.example',
            with:  ['mailData' => $this->mailData],
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
