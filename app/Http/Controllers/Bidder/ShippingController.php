<?php

namespace App\Http\Controllers\Bidder;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Notifications\ShipmentReceivedNotification;

class ShippingController extends Controller
{
    /**
     * RajaOngkir sudah mengembalikan list flat:
     * [
     *   { name, code, service, description, cost, etd }, ...
     * ]
     * Kita hanya pastikan field-nya konsisten.
     */
    protected function mapOptions(array $data): array
    {
        return array_map(function ($row) {
            return [
                'code'        => $row['code']        ?? '',
                'name'        => $row['name']        ?? '',
                'service'     => $row['service']     ?? ($row['service_code'] ?? ''),
                'description' => $row['description'] ?? '',
                'cost'        => (int) ($row['cost'] ?? 0),
                'etd'         => $row['etd']         ?? ($row['etd_day'] ?? null),
            ];
        }, $data);
    }

    protected function ensureEditable(Payment $payment): void
    {
        $userId = auth()->id();

        abort_unless(optional($payment->bidderProfile)->user_id === $userId, 403);

        // Kalau UI_DEV_MODE = true -> jangan terlalu saklek status PENDING
        if (! config('rajaongkir.ui_dev_mode')) {
            $isExpired = $payment->expires_at
                && now()->gt($payment->expires_at)
                && $payment->status !== 'PAID';

            abort_if($payment->status !== 'PENDING' || $isExpired, 422, 'Transaksi sudah tidak bisa diubah.');
        }
    }

    /* ---------- Dropdown alamat dari RajaOngkir ---------- */

    public function provinces(RajaOngkirService $ro)
    {
        try {
            return response()->json($ro->provinces());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 502);
        }
    }

    public function cities(Request $request, RajaOngkirService $ro)
    {
        $request->validate(['province_id' => 'required|integer']);

        return response()->json($ro->cities((int) $request->province_id));
    }

    public function districts(Request $request, RajaOngkirService $ro)
    {
        $request->validate(['city_id' => 'required|integer']);

        return response()->json($ro->districts((int) $request->city_id));
    }

    /* ---------- Ubah alamat pengiriman untuk 1 payment ---------- */
    public function updateAddress(Payment $payment, Request $request)
    {
        $this->ensureEditable($payment);

        $data = $request->validateWithBag('shippingAddress', [
            'address'        => 'required|string|max:500',
            'province_id'    => 'required|integer',
            'province_name'  => 'required|string|max:100',
            'city_id'        => 'required|integer',
            'city_name'      => 'required|string|max:100',
            'district_id'    => 'required|integer',
            'district_name'  => 'required|string|max:100',
            'postal_code'    => 'required|string|max:20',
            'phone'          => ['required','regex:/^[0-9]{9,13}$/'],
        ]);

        // Normalisasi phone: simpan jadi +62xxxxxxxxxx
        $phoneDigits = preg_replace('/\D/', '', $data['phone']); // harusnya sudah angka
        if (str_starts_with($phoneDigits, '0')) {
            $phoneDigits = ltrim($phoneDigits, '0');
        }
        if (!str_starts_with($phoneDigits, '62')) {
            // input kamu biasanya mulai 8xxxx
            $phoneNormalized = '+62'.$phoneDigits;
        } else {
            $phoneNormalized = '+'.$phoneDigits;
        }

        $payment->update([
            // alamat
            'address'                         => $data['address'],
            'province'                        => $data['province_name'],
            'city'                            => $data['city_name'],
            'district'                        => $data['district_name'],
            'postal_code'                     => $data['postal_code'],
            'phone'                           => $phoneNormalized,

            // rajaongkir destination id (yang dipakai hitung ongkir)
            'shipping_rajaongkir_district_id' => (int) $data['district_id'],

            // ✅ reset ongkir karena tujuan pengiriman berubah
            'shipping_courier'      => null,
            'shipping_service'      => null,
            'shipping_fee'          => 0,
            'shipping_etd'          => null,
            'shipping_raw_response' => null,
        ]);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Alamat pengiriman berhasil diperbarui. Silakan hitung ulang ongkos kirim.',
        ]);
    }

    /* ---------- Hitung ongkir untuk 1 payment ---------- */

    public function options(Payment $payment, Request $request, RajaOngkirService $ro)
    {
        $this->ensureEditable($payment);

        // berat diambil dari payment; request weight optional untuk masa depan
        $data = $request->validate([
            'weight' => 'nullable|integer|min:1',
        ]);

        $weight = $data['weight']
            ?? $payment->shipping_weight
            ?? config('rajaongkir.default_weight');

        if (! $payment->shipping_rajaongkir_district_id) {
            return response()->json([
                'message' => 'Alamat pengiriman belum lengkap. Silakan ubah alamat terlebih dahulu.',
            ], 422);
        }

        $payment->shipping_weight = $weight;
        $payment->save();

        $origin      = (int) config('rajaongkir.origin_district_id');
        $destination = (int) $payment->shipping_rajaongkir_district_id;

        try {
            $optionsRaw = $ro->calculateDomesticCostByDistrict($origin, $destination, (int) $weight);
            $options    = $this->mapOptions($optionsRaw);

            $allowed = [
                'jne' => ['REG'], // kalau mau tambah YES tinggal ['REG','YES']
                'jnt' => ['EZ'],
            ];

            $options = array_values(array_filter($options, function ($opt) use ($allowed) {
                $code = strtolower($opt['code'] ?? '');
                $svc  = strtoupper($opt['service'] ?? '');

                return isset($allowed[$code]) && in_array($svc, $allowed[$code], true);
            }));
        } catch (\Throwable $e) {
            Log::error('RajaOngkir calculate error', [
                'payment_id'  => $payment->id,
                'origin'      => $origin,
                'destination' => $destination,
                'weight'      => $weight,
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal menghitung ongkos kirim: ' . $e->getMessage(),
            ], 500);
        }

        // simpan FLAT options di payment supaya reload halaman tidak perlu hit API lagi
        $payment->shipping_raw_response = $options;
        $payment->save();

        return response()->json([
            'data' => $options,
        ]);
    }

    public function select(Payment $payment, Request $request)
    {
        $this->ensureEditable($payment);

        $data = $request->validate([
            'courier_code' => 'required|string',
            'service'      => 'required|string',
            'cost'         => 'required|integer|min:0',
            'etd'          => 'nullable|string',
        ]);

        $payment->update([
            'shipping_courier' => $data['courier_code'],
            'shipping_service' => $data['service'],
            'shipping_fee'     => $data['cost'],
            'shipping_etd'     => $data['etd'] ?? null,
        ]);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Ongkos kirim berhasil dikonfirmasi.',
        ]);
    }

    public function complete(Request $request, Payment $payment)
    {
        $user = $request->user();

        // pastikan ini punya si bidder sendiri
        if ($payment->bidderProfile?->user_id !== $user->id) {
            abort(403);
        }

        if ($payment->shipping_status !== 'SHIPPED') {
            return back()->with('error', 'Pesanan belum dalam status sedang dikirim.');
        }

        $payment->update([
            'shipping_status'       => 'COMPLETED',
            'shipping_completed_at' => now(),
        ]);

        // 🔔 Kirim notifikasi bahwa barang telah diterima
        $buyerUser = $payment->user;
        if ($buyerUser) {
            $buyerUser->notify(new ShipmentReceivedNotification($payment));
        }

        return back()->with('success', 'Terima kasih, pesanan ditandai selesai & notifikasi telah dikirim.');
    }
}
