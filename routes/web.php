<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\LotController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PaymentController;

use App\Http\Controllers\Bidder\MyAuctionController;
use App\Http\Controllers\Bidder\TransactionController;
use App\Http\Controllers\Bidder\PaymentCheckoutController;
use App\Http\Controllers\Bidder\ShippingController;

use App\Models\User;
use App\Models\Bid;

//Auth Routes (Jetstream/Fortify)
require __DIR__.'/auth.php';

//Authenticated common routes (profile + dashboard redirect)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // OVERRIDE profile.show bawaan Jetstream
    Route::get('/user/profile', function () {
        $user = auth()->user();

        if ($user && $user->isAdmin()) {
            return view('profile.show'); // admin pakai layout app
        }

        return view('bidder.profile.show'); // bidder pakai layout guest
    })->name('profile.show');

    // Redirect dashboard Jetstream berdasarkan role
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if (! $user) return redirect()->route('home');

        if ($user->isAdmin())  return redirect()->route('admin.dashboard');
        if ($user->isBidder()) return redirect()->route('home');

        return redirect()->route('home');
    })->name('dashboard');
});

//GUEST & BIDDER AREA
Route::get  ('/',                     [HomeController::class, 'index'])->name('home');
Route::get  ('/lots/{lot}',           [HomeController::class, 'show'])->name('lots.show'); 
Route::get  ('/lots/{lot}/poll',      [HomeController::class, 'poll'])->name('lots.poll');
Route::post ('/newsletter/subscribe', [HomeController::class, 'subscribeNewsletter'])->name('newsletter.subscribe');
Route::view ('/panduan-dan-aturan', 'public.rules')->name('rules');
Route::view ('/tentang-kami',       'public.about')->name('about');
Route::view ('/terms-of-service',   'terms')->name('terms.show');
Route::view ('/privacy-policy',     'policy')->name('policy.show');

Route::get  ('/check-username', function (Request $request) {
    $exists = User::where('username', $request->get('username'))->exists();
    return response()->json(['exists' => $exists]);
})->name('check.username');

Route::get  ('/check-email', function (Request $request) {
    $exists = User::where('email', $request->get('email'))->exists();
    return response()->json(['exists' => $exists]);
})->name('check.email');

Route::post ('/email/update', function (Request $request) {
    $request->validate([
        'email' => 'required|email|unique:users,email',
    ]);

    $user = Auth::user();
    $user->email = $request->email;
    $user->email_verified_at = null;
    $user->save();

    // Kirim ulang email verifikasi
    $user->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
})->middleware(['auth'])->name('email.update');

//BIDDER AREA
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    Route::post ('/lots/{lot}/bid',  [HomeController::class, 'bid'])->name('lots.bid')->middleware('throttle.bid');

    // LELANG SAYA
    Route::get ('/my/auctions',      [MyAuctionController::class, 'index'])->name('my.auctions');
    Route::get ('/my-auctions/poll', [MyAuctionController::class, 'poll'])->name('bids.poll');
    Route::post('/watchlist/{lot}',  [MyAuctionController::class, 'toggle'])->name('watchlist.toggle')->middleware('can-bid');

    // TRANSAKSI SAYA
    Route::get ('/my/transactions',           [TransactionController::class, 'index'])->name('transactions.index');
    Route::get ('/my/transactions/{payment}', [TransactionController::class, 'show'])->name('transactions.show');
});

//Payment Gateway - Duitku (auth + verified)
Route::middleware(['auth', 'verified'])
    ->prefix('checkout')
    ->group(function () {
        Route::get ('{payment}',        [PaymentCheckoutController::class, 'show'])->name('checkout.show');
        Route::get ('{payment}/return', [PaymentCheckoutController::class, 'return'])->name('checkout.return'); // return URL setelah bayar 
    });

//RajaOngkir (auth + verified)
Route::middleware  (['auth', 'verified'])->group(function () {
    Route::prefix  ('ajax/rajaongkir')->name('rajaongkir.')->group(function () {
        Route::get ('provinces', [ShippingController::class, 'provinces'])->name('provinces');
        Route::get ('cities',    [ShippingController::class, 'cities'])->name('cities');
        Route::get ('districts', [ShippingController::class, 'districts'])->name('districts');
    });

    Route::post ('/transactions/{payment}/shipping/address',     [ShippingController::class, 'updateAddress'])->name('shipping.address.update');
    Route::get  ('/transactions/{payment}/shipping/options',     [ShippingController::class, 'options'])->name('shipping.options');
    Route::post ('/transactions/{payment}/shipping/select',      [ShippingController::class, 'select'])->name('shipping.select');
    Route::post ('/my/transactions/{payment}/shipping/complete', [ShippingController::class, 'complete'])->name('shipping.complete');
});

//ADMIN AREA
Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])  ->group(function () {
    // DASHBOARD
    Route::get   ('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // PRODUK
    Route::get   ('/products',                       [ProductController::class, 'index'])->name('products.index');
    Route::post  ('/products',                       [ProductController::class, 'store'])->name('products.store');
    Route::get   ('/products/{product}',             [ProductController::class, 'show'])->name('products.show');
    //Route::delete('/products/bulk-destroy',          [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
    Route::post  ('/products/{product}/images/sync', [ProductController::class, 'syncImages'])->name('products.images.sync');
    Route::post  ('/products/{product}/images',      [ProductController::class, 'addImage'])->name('products.images.add');
    Route::put   ('/products/{product}',             [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}',             [ProductController::class, 'destroy'])->whereNumber('product')->name('products.destroy');

    // LOT
    Route::get   ('/lots',                [LotController::class,'index'])->name('lots.index');
    Route::get   ('/lots/{lot}',          [LotController::class, 'show'])->name('lots.detail');
    Route::post  ('/lots',                [LotController::class,'store'])->name('lots.store');
    Route::put   ('/lots/{lot}',          [LotController::class,'update'])->name('lots.update');
    Route::delete('/lots/{lot}',          [LotController::class,'destroy'])->name('lots.destroy');
    Route::post  ('/lots/{lot}/cancel',   [LotController::class,'cancel'])->name('lots.cancel');
    Route::post  ('/lots/{lot}/unsold',   [LotController::class,'closeAsUnsold'])->name('lots.unsold');
    Route::post  ('/lots/{lot}/awarded',  [LotController::class,'closeAsAwarded'])->name('lots.awarded');

    // PENGGUNA
    Route::get   ('/users',                [UserController::class, 'index'])->name('users.index');
    Route::get   ('/users/{user}',         [UserController::class, 'show'])->name('users.show');
    Route::post  ('/users/admin',          [UserController::class, 'storeAdmin'])->name('users.store-admin');
    Route::patch ('/users/{user}/status',  [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post  ('/users/{user}/reset',   [UserController::class, 'sendPasswordReset'])->name('users.send-reset');
    Route::post  ('/users/{user}/verify',  [UserController::class, 'resendVerification'])->name('users.resend-verification');
    Route::patch ('/users/{user}/suspend', [UserController::class, 'suspendWithReason'])->name('users.suspend-with-reason');

    // TRANSAKSI
    Route::get   ('/transactions',                     [PaymentController::class, 'index'])->name('payments.index');
    Route::get   ('/transactions/{payment}',           [PaymentController::class, 'show'])->name('payments.show');
    Route::post  ('/transactions/{payment}/mark-paid', [PaymentController::class, 'markPaid'])->name('payments.mark-paid');
    Route::patch ('/transactions/{payment}/shipping',  [PaymentController::class, 'updateShipping'])->name('payments.update-shipping');

});
