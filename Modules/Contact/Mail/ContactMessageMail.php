<?php

declare(strict_types=1);

namespace Modules\Contact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Contact\Data\ContactSubmissionData;

final class ContactMessageMail extends Mailable implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public readonly ContactSubmissionData $submission)
    {
        $this->onQueue('mail');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->submission->email, $this->submission->name)],
            subject: 'Nouveau message depuis le portfolio',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'contact::mail.message',
        );
    }
}
