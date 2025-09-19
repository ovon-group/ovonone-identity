<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Spatie\OneTimePasswords\Models\OneTimePassword;
use Spatie\OneTimePasswords\Notifications\OneTimePasswordNotification as BaseOneTimePasswordNotification;

class OneTimePasswordNotification extends BaseOneTimePasswordNotification
{
    use Queueable;

    public function __construct(
        public OneTimePassword $oneTimePassword,
        public string $channel = 'mail'
    ) {
        parent::__construct($oneTimePassword);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($this->channel === 'sms' && $notifiable->mobile) {
            $channels[] = TwilioChannel::class;
        } else {
            // Default to mail
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your One-Time Password')
            ->greeting('Hello!')
            ->line('You are receiving this email because we received a login request for your account.')
            ->line("Your one-time password is: **{$this->oneTimePassword->password}**")
            ->line("This password will expire in 2 minutes.")
            ->line('If you did not request this login, no further action is required.')
            ->salutation('Regards, ' . config('app.name'));
    }

    /**
     * Get the Twilio SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): TwilioSmsMessage
    {
        return TwilioSmsMessage::create()
            ->content("Your " . config('app.name') . " one-time password is: {$this->oneTimePassword->password}. This code will expire in 2 minutes.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'password' => $this->oneTimePassword->password,
            'expires_at' => $this->oneTimePassword->expires_at,
        ];
    }
}
