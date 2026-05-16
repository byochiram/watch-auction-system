<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Models\Payment;
use App\Notifications\AuctionWonNotification;
use App\Notifications\AuctionLostNotification;

class AuctionLot extends Model
{
    protected $fillable = [
        'product_id','start_price','increment','current_price',
        'start_at','end_at','cancelled_at','cancel_reason','winner_bid_id'
    ];

    protected $casts = [
        'start_price'  =>'decimal:2',
        'increment'    =>'decimal:2',
        'current_price'=>'decimal:2',
        'start_at'     =>'datetime',
        'end_at'       =>'datetime',
        'cancelled_at' =>'datetime',
    ];

    // Runtime status utama: SCHEDULED / ACTIVE / ENDED / CANCELLED
    public function getRuntimeStatusAttribute(): string
    {
        $now = now();

        // 1. Kalau batal, ya CANCELLED
        if ($this->cancelled_at) {
            return 'CANCELLED';
        }

        // 2. Belum mulai
        if ($now->lt($this->start_at)) {
            return 'SCHEDULED';
        }

        // 3. Sedang jalan
        if ($now->between($this->start_at, $this->end_at)) {
            return 'ACTIVE';
        }

        // 4. Di bawah ini pasti: sudah lewat end_at (after auction)

        // Kalau tidak ada pemenang & tidak ada payment → tidak ada bid sama sekali
        if (! $this->winner_bid_id && ! $this->payment) {
            return 'ENDED'; // berakhir tanpa bid
        }

        // Kalau ada payment, cek statusnya
        if ($this->payment) {
            return match ($this->payment->status) {
                'PENDING'  => 'PENDING',  // menunggu pembayaran
                'PAID'     => 'AWARDED',  // terjual, pembayaran beres
                'EXPIRED',
                'CANCELLED' => 'UNSOLD',  // pembayaran gagal / tidak dilakukan
                default    => 'ENDED',    // fallback
            };
        }

        // Default (harusnya jarang masuk)
        return 'ENDED';
    }

    // Scopes bantu (pakai runtime rules di query)
    public function scopeCancelled($q)
    {
        return $q->whereNotNull('cancelled_at');
    }

    public function scopeNotCancelled($q)
    {
        return $q->whereNull('cancelled_at');
    }
    
    public function scopeScheduled($q)
    {
        return $q->notCancelled()
                 ->where('start_at', '>', now());
    }

    public function scopeActive($q)
    {
        return $q->notCancelled()
                 ->where('start_at', '<=', now())
                 ->where('end_at', '>=', now());
    }

    public function scopeEnded($q)
    {
        return $q->notCancelled()
                 ->where('end_at', '<', now());
    }

    // Relasi
    public function product()
    { 
        return $this->belongsTo(Product::class); 
    }
    
    public function bids()
    { 
        return $this->hasMany(Bid::class,'lot_id'); 
    }

    public function payment()
    { 
        return $this->hasOne(Payment::class,'lot_id'); 
    }
    
    public function winnerBid()
    { 
        return $this->belongsTo(Bid::class,'winner_bid_id'); 
    }

    //helper: akses bidder & user pemenang secara praktis
    public function getWinnerProfileAttribute()
    {
        return $this->winnerBid?->bidderProfile;
    }

    public function getWinnerUserAttribute()
    {
        return $this->winnerBid?->bidderProfile?->user;
    }
    
    public function watchlists()
    {
        return $this->hasMany(Watchlist::class, 'lot_id');
    }

    public function watchers()
    {
        return $this->belongsToMany(
            BidderProfile::class,
            'watchlists',
            'lot_id',
            'bidder_profile_id'
        )->withTimestamps();
    }

     /**
     * Proses otomatis ketika lelang ini sudah berakhir.
     * - Kalau ada bid: tetapkan pemenang & buat invoice (kalau belum ada).
     * - Kalau tidak ada bid: tidak melakukan apa-apa (unsold).
     */
    public function processAfterEnd(): void
    {
        // kalau dibatalkan, jangan diproses
        if ($this->cancelled_at) {
            return;
        }

        // kalau belum benar-benar berakhir, skip
        if (now()->lt($this->end_at)) {
            return;
        }

        // kalau sudah pernah ada pemenang, jangan diproses ulang
        if ($this->winner_bid_id || $this->payment) {
            return;
        }

        // ambil bid tertinggi (kalau nilai sama, pakai yang paling awal)
        $highestBid = $this->bids()
            ->orderByDesc('amount')
            ->orderBy('created_at')   // bid lebih cepat menang kalau jumlah sama
            ->first();

        if (! $highestBid) {
            // tidak ada bid => lot berakhir tanpa pemenang
            // kalau nanti mau tandai UNSOLD di kolom status, bisa di-handle di sini.
            return;
        }

        // set pemenang
        $this->winner_bid_id = $highestBid->id;
        $this->current_price = $highestBid->amount; 
        $this->save();

        // UPDATE STATISTIK BIDDER: win_count + 1
        $profile = $highestBid->bidderProfile;
        if ($profile) {
            $profile->increment('win_count');   
        }

        // buat invoice untuk pemenang, kalau belum ada
        $payment = $this->payment;
        if (! $payment) {
            $payment = Payment::createForWinner($this);
        }

        // Kirim notifikasi ke pemenang (kalau user-nya valid & email ada)
        $winnerUser = $this->winnerUser; // accessor yang sudah ada di model
        if ($winnerUser && $payment) {
            $winnerUser->notify(new AuctionWonNotification($this, $payment));
        }

        // ambil semua user yang pernah bid
        $bidderUsers = $this->bids
            ->map(fn ($bid) => $bid->bidderProfile?->user)
            ->filter()
            ->unique('id');

        // exclude pemenang
        $bidderUsers = $bidderUsers->reject(
            fn ($user) => $winnerUser && $user->id === $winnerUser->id
        );

        foreach ($bidderUsers as $user) {
            // key unik per lot + user
            $cacheKey = "auction_loser_notified:lot_{$this->id}:user_{$user->id}";

            // hanya kirim kalau belum pernah
            if (Cache::add($cacheKey, true, now()->addDays(30))) {
                $user->notify(new AuctionLostNotification($this));
            }
        }
    }
}
