<?php

namespace App\Notifications;

use App\Models\AuctionLot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionCancelledNotification extends Notification implements ShouldQueue
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
        $title = trim(($product?->brand ?? '') . ' ' . ($product?->model ?? ''))
            ?: 'Lot #' . $this->lot->id;

        return (new MailMessage)
            ->subject('Tempus Auctions — Pemberitahuan Pembatalan Lelang')
            ->greeting('Halo ' . ($notifiable->name ?? '') . ',')
            ->line('Kami informasikan bahwa lelang berikut telah dibatalkan:')
            ->line("**{$title}** (Lot #{$this->lot->id})")
            ->line(' ')
            ->line('Alasan pembatalan:')
            ->line($this->lot->cancel_reason ?: '-')
            ->line(' ')
            ->line('Kami mohon maaf atas ketidaknyamanan yang ditimbulkan.')
            ->line('Seluruh bid yang telah diajukan pada lelang ini dinyatakan tidak berlaku.')
            ->line(' ')
            ->line('Terima kasih atas partisipasi dan kepercayaan Anda menggunakan Tempus Auctions.');
    }
}

