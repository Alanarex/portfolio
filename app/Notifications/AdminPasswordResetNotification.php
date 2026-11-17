<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * E-mail de réinitialisation : lien signé (HMAC, expirant) portant un jeton à usage unique stocké haché.
 * Mis en file : le temps de réponse de la demande ne dépend pas de l'existence du compte.
 */
final class AdminPasswordResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token, string $locale)
    {
        $this->locale($locale);
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = (int) config('auth.passwords.users.expire', 30);
        $locale = (string) $this->locale;

        $root = app()->isProduction() ? (string) config('app.url') : null;
        if ($root !== null) {
            URL::forceRootUrl($root);
        }
        $url = URL::temporarySignedRoute('password.reset', now()->addMinutes($minutes), [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
        if ($root !== null) {
            URL::forceRootUrl(null);
        }

        return (new MailMessage)
            ->subject(__('auth.mail.subject', [], $locale))
            ->greeting(__('auth.mail.greeting', [], $locale))
            ->line(__('auth.mail.intro', [], $locale))
            ->action(__('auth.mail.action', [], $locale), $url)
            ->line(__('auth.mail.expires', ['minutes' => $minutes], $locale))
            ->line(__('auth.mail.ignore', [], $locale))
            ->salutation(' ');
    }
}
