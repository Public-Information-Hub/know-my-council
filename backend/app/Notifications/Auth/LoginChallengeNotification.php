<?php

namespace App\Notifications\Auth;

use App\Models\UserAuthChallenge;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginChallengeNotification extends Notification
{
    public function __construct(
        public readonly UserAuthChallenge $challenge,
        public readonly ?string $code,
        public readonly ?string $magicLink
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Your KnowMyCouncil sign-in check')
            ->line('We detected a sign-in that needs an extra check.');

        if ($this->code !== null) {
            $message->line("Your verification code is: {$this->code}");
        }

        if ($this->magicLink !== null) {
            $message->action('Sign in with the magic link', $this->magicLink);
        }

        return $message
            ->line('This check expires soon. If you did not try to sign in, you can ignore this email.');
    }
}
