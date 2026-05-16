<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionLot;
use App\Models\Product;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $me = auth()->user();
        $today = Carbon::today();

        // Statistik lot
        $totalLots       = AuctionLot::count();
        $activeLotsCount = AuctionLot::active()->count();
        $scheduledLots   = AuctionLot::scheduled()->count();
        $endedLots       = AuctionLot::ended()->count();
        $cancelledLots   = AuctionLot::cancelled()->count();

        // Statistik produk & user
        $totalProducts = Product::count();
        $totalUsers    = User::count();

        $newBiddersToday = User::where('role', 'BIDDER')
            ->whereDate('created_at', $today)
            ->count();

        $latestLots = AuctionLot::orderByDesc('created_at')->take(5)->get();

        // Aktivitas: Lelang, Produk, Pengguna, Transaksi
        $activityLots = AuctionLot::orderByDesc('updated_at')->take(5)->get()->map(function ($lot) {
            return [
                'type'       => 'Lelang',
                'name'       => $lot->title ?? ('Lot #'.$lot->id),
                'status'     => $lot->runtime_status ?? null,
                'created_at' => $lot->created_at,
                'updated_at' => $lot->updated_at,
            ];
        });

        $activityProducts = Product::orderByDesc('updated_at')->take(5)->get()->map(function ($product) {
            $name = trim(($product->brand ?? '').' '.($product->model ?? ''));
            if ($name === '') $name = 'Produk #'.$product->id;

            return [
                'type'       => 'Produk',
                'name'       => $name,
                'status'     => null,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        });

        $activityUsers = User::orderByDesc('updated_at')->take(5)->get()->map(function ($user) {
            $name = $user->username ?? $user->name ?? ('User #'.$user->id);

            return [
                'type'       => 'Pengguna',
                'name'       => $name,
                'status'     => $user->status ?? null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        $activityPayments = Payment::orderByDesc('updated_at')->take(5)->get()->map(function ($pay) {
            return [
                'type'       => 'Transaksi',
                'name'       => $pay->invoice_no ?? ('Payment #'.$pay->id),
                'status'     => $pay->status ?? null,
                'created_at' => $pay->created_at,
                'updated_at' => $pay->updated_at,
            ];
        });

        $activities = collect()
            ->merge($activityLots)
            ->merge($activityProducts)
            ->merge($activityUsers)
            ->merge($activityPayments)
            ->sortByDesc('updated_at')
            ->take(5);

        // Panel perhatian
        $upcomingLots24h = AuctionLot::scheduled()
            ->whereBetween('start_at', [now(), now()->addDay()])
            ->count();

        $lotsEndingToday = AuctionLot::whereDate('end_at', $today)->count();

        // Statistik transaksi
        $paymentCounts = Payment::select('status', DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        $pendingPayments = (int) ($paymentCounts['PENDING'] ?? 0);
        $paidPayments    = (int) ($paymentCounts['PAID'] ?? 0);
        $totalPayments   = (int) $paymentCounts->sum();

        $paidAmountThisMonth = (float) Payment::where('status','PAID')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount_due');

        $invoicesExpiringSoon = Payment::where('status','PENDING')
            ->whereNull('paid_at')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addHour()])
            ->count();

        return view('admin.dashboard', compact(
            'me','today',
            'totalLots','activeLotsCount','scheduledLots','endedLots','cancelledLots',
            'totalProducts','totalUsers','newBiddersToday',
            'latestLots','activities',
            'upcomingLots24h','lotsEndingToday',
            'pendingPayments','paidPayments','totalPayments',
            'paidAmountThisMonth','invoicesExpiringSoon'
        ));
    }
}
