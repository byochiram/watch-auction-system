<?php

namespace App\Notifications;

use App\Models\AuctionLot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionLostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public AuctionLot $lot) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $product = $this->lot->product;
        $title   = trim(($product?->brand ?? '').' '.($product?->model ?? ''))
                    ?: 'Lot #'.$this->lot->id;

        return (new MailMessage)
            ->subject('Tempus Auctions — Hasil Lelang')
            ->greeting('Halo '.$notifiable->name.',')
            ->line("Lelang berikut telah berakhir:")
            ->line("**{$title}** (Lot #{$this->lot->id})")
            ->line('Sayangnya, bid Anda belum berhasil memenangkan lelang ini.')
            ->line('Terima kasih telah berpartisipasi. Kami harap Anda menemukan lot menarik lainnya di Tempus Auctions.')
            ->action('Lihat Lelang Lain', route('home'));
    }
}
