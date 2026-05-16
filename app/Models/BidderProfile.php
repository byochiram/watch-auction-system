<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BidderProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        // 'address',
        // 'city',
        // 'district',
        // 'village',
        // 'province',
        // 'postal_code',
        // 'verified_at',
        'bid_count',
        'win_count',
        'total_spent',
        'last_bid_at',
    ];

    protected $casts = [
        //'verified_at' => 'datetime',
        'total_spent' => 'decimal:2',
        'last_bid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // relasi ke master Indonesia (pakai kolom code)
    // public function provinceRef()
    // {
    //     return $this->belongsTo(Province::class, 'province', 'code');
    // }

    // public function cityRef()
    // {
    //     return $this->belongsTo(City::class, 'city', 'code');
    // }

    // public function districtRef()
    // {
    //     return $this->belongsTo(District::class, 'district', 'code');
    // }

    // public function villageRef()
    // {
    //     return $this->belongsTo(Village::class, 'village', 'code');
    // }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }
    
    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function watchlistLots()
    {
        return $this->belongsToMany(
            AuctionLot::class,
            'watchlists',
            'bidder_profile_id',
            'lot_id'
        )->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
