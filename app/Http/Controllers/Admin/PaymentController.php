<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ShipmentOnTheWayNotification;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $per = in_array((int) $request->per, [10, 25, 50]) ? (int) $request->per : 10;

        // tab: pending | paid | expired
        $tab = $request->input('tab');
        if (! in_array($tab, ['pending', 'paid', 'expired'], true)) {
            $tab = 'pending';
            $request->merge(['tab' => $tab]);
        }

        $search = $request->get('search');
        $search = is_string($search) ? trim($search) : null;

        $sort   = $request->input('sort');
        if (! in_array($sort, ['issued_asc','amount_desc','amount_asc'], true)) {
            $sort = null;
        }

        // BASE QUERY
        $query = Payment::query()
            ->with([
                'bidderProfile.user',
                'lot.product',
            ])
            ->when($search !== null && $search !== '', function ($q) use ($search) {
                $like = "%{$search}%";

                $q->where(function ($qq) use ($like) {
                    // invoice_no
                    $qq->where('invoice_no', 'like', $like)

                        // nama / email / username buyer
                        ->orWhereHas('bidderProfile', function ($bq) use ($like) {
                            $bq->whereHas('user', function ($uq) use ($like) {
                                $uq->where('name', 'like', $like)
                                ->orWhere('email', 'like', $like)
                                ->orWhere('username', 'like', $like);
                            });
                        })

                        // brand / model produk (nama lelang)
                        ->orWhereHas('lot', function ($lq) use ($like) {
                            $lq->whereHas('product', function ($pq) use ($like) {
                                $pq->where('brand', 'like', $like)
                                ->orWhere('model', 'like', $like);
                            });
                        });
                });
            });

        // map tab → status
        $statusMap = [
            'pending' => 'PENDING',
            'paid'    => 'PAID',
            'expired' => 'EXPIRED',
        ];
        $status = $statusMap[$tab];

        // QUERY UTAMA SESUAI TAB + SORT
        $payments = $query
            ->where('status', $status)
            ->when($sort === 'issued_asc',  fn($q) => $q->orderBy('issued_at', 'asc'))
            ->when($sort === 'amount_desc', fn($q) => $q->orderBy('amount_due', 'desc'))
            ->when($sort === 'amount_asc',  fn($q) => $q->orderBy('amount_due', 'asc'))
            ->when(! $sort,                fn($q) => $q->orderBy('issued_at', 'desc'))
            ->paginate($per)
            ->withQueryString();

        $stats = [
            'total'   => Payment::count(),
            'pending' => Payment::where('status', 'PENDING')->count(),
            'paid'    => Payment::where('status', 'PAID')->count(),
            'expired' => Payment::where('status', 'EXPIRED')->count(),
        ];

        return view('admin.transactions.index', [
            'tab'      => $tab,
            'payments' => $payments,
            'stats'    => $stats,
            'perPage'  => $per,
            'search'   => $search,
            'sort'     => $sort,
        ]);
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'bidderProfile',       
            'lot.product',
        ]);

        return view('admin.transactions.show', compact('payment'));
    }

    public function updateShipping(Request $request, Payment $payment)
    {
        $validator = Validator::make($request->all(), [
            'shipping_courier'     => ['required', 'string', 'max:20'],
            'shipping_tracking_no' => ['required', 'string', 'max:50'],
        ], [
            'shipping_courier.required' => 'Kurir wajib diisi.',
            'shipping_courier.max'      => 'Nama kurir maksimal 20 karakter.',
            'shipping_tracking_no.required' => 'Nomor resi wajib diisi.',
            'shipping_tracking_no.max'      => 'Nomor resi maksimal 50 karakter.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'ship')
                ->with('ship_payment_id', $payment->id);
        }

        $data = $validator->validated();

        $payment->update([
            'shipping_courier'      => $data['shipping_courier'],
            'shipping_tracking_no'  => $data['shipping_tracking_no'],
            'shipping_status'       => 'SHIPPED',
            'shipping_shipped_at'   => $payment->shipping_shipped_at ?: now(),
        ]);

        $user = $payment->user;
        if ($user) {
            $user->notify(new ShipmentOnTheWayNotification(
                payment: $payment,
                courier: $payment->shipping_courier,
                trackingNumber: $payment->shipping_tracking_no,
            ));
        }

        return redirect()
            ->route('payments.index', ['tab' => 'paid'])
            ->with('success', 'Informasi pengiriman berhasil disimpan & notifikasi dikirim ke pemenang.');
    }
}
