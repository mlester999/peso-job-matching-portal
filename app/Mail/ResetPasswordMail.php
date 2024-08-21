<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(private string $token, private string $firstName, private string $email)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PESO Cabuyao - Reset Password',
        );
    }

    public function build()
    {
        $frontendUrl = 'http://localhost:3000/reset-password'; // Frontend URL
        $resetLink = $frontendUrl . '?token=' . $this->token . '&email=' . $this->email;

        return $this->markdown('mail.reset-password')
            ->with([
                'resetLink' => $resetLink,
                'firstName' => $this->firstName,
                'email' => $this->email,
            ]);
    }

    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'mail.reset-password',
    //         with: ['token' => $this->token, 'verificationCode' => $this->verificationCode]
    //     );
    // }

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
