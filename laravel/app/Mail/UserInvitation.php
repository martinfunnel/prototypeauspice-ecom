<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $identifier,
        public string $resetUrl,
        public string $fullName,
    ) {}

    public function build(): static
    {
        return $this->subject('Votre compte Auspice Market - Définissez votre mot de passe')
            ->view('emails.user-invitation')
            ->with([
                'identifier' => $this->identifier,
                'resetUrl' => $this->resetUrl,
                'fullName' => $this->fullName,
            ]);
    }
}
