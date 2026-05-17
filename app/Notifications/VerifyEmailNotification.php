<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends BaseVerifyEmail
{
    protected function verificationUrl($notifiable): string
{
    $path = URL::temporarySignedRoute(
        'verification.verify',
        Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
        [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ],
        false
    );

    return rtrim(config('app.url'), '/') . $path;
}
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tempus Auctions - Verifikasi Email')
            ->greeting('Halo ' . ($notifiable->name ?: ''))
            ->line('Terima kasih telah mendaftar di Tempus Auctions.')
            ->line('Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda.')
            ->action('Verifikasi Email', $this->verificationUrl($notifiable))
            ->line('Jika Anda tidak membuat akun di Tempus Auctions, abaikan email ini.');
    }
}
