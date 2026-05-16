<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('rajaongkir.base_url'), '/');
        $this->apiKey  = (string) config('rajaongkir.key');
    }

    protected function client()
    {
        return Http::withHeaders([
                'Key'    => $this->apiKey,
                'Accept'        => 'application/json',
            ])
            ->baseUrl($this->baseUrl);
    }

    protected function useMock(): bool
    {
        // Kalau RAJAONGKIR_ENABLED=false di .env -> pakai dummy data, tidak hit API beneran
        return ! config('rajaongkir.enabled', true);
    }

    /* --------------------------------------
     *  PROVINSI
     * ----------------------------------- */

    public function provinces(): array
    {
        if ($this->useMock()) {
            return $this->mockProvinces();
        }

        $ttl = now()->addSeconds(config('rajaongkir.cache_ttl.province', 60 * 60 * 24 * 7));

        return Cache::remember('rajaongkir:provinces', $ttl, function () {
            $res = $this->client()->get('/destination/province');

            Log::debug('RajaOngkir raw', [
                'endpoint' => '/destination/province',
                'status'   => $res->status(),
                'body'     => $res->body(),
            ]);

            if (! $res->successful()) {
                throw new \RuntimeException('Gagal mengambil provinsi dari RajaOngkir.');
            }

            $json = $res->json();

            return $json['data'] ?? [];
        });
    }

    /* --------------------------------------
     *  KOTA / KABUPATEN
     * ----------------------------------- */

    public function cities(int $provinceId): array
    {
        if ($this->useMock()) {
            return $this->mockCities($provinceId);
        }

        $ttl = now()->addSeconds(config('rajaongkir.cache_ttl.city', 60 * 60 * 24 * 7));
        $key = "rajaongkir:cities:{$provinceId}";

        return Cache::remember($key, $ttl, function () use ($provinceId) {
            $endpoint = "/destination/city/{$provinceId}";
            $res = $this->client()->get($endpoint);

            Log::debug('RajaOngkir raw', [
                'endpoint'    => '/city',
                'status'      => $res->status(),
                'body'        => $res->body(),
                'province_id' => $provinceId,
            ]);

            if (! $res->successful()) {
                throw new \RuntimeException('Gagal mengambil kota/kabupaten dari RajaOngkir.');
            }

            $json = $res->json();

            return $json['data'] ?? [];
        });
    }

    /* --------------------------------------
     *  KECAMATAN
     * ----------------------------------- */

    public function districts(int $cityId): array
    {
        if ($this->useMock()) {
            return $this->mockDistricts($cityId);
        }

        $ttl = now()->addSeconds(config('rajaongkir.cache_ttl.district', 60 * 60 * 24 * 7));
        $key = "rajaongkir:districts:{$cityId}";

        return Cache::remember($key, $ttl, function () use ($cityId) {
            $endpoint = "/destination/district/{$cityId}";
            $res = $this->client()->get($endpoint);

            Log::debug('RajaOngkir raw', [
                'endpoint' => '/district',
                'status'   => $res->status(),
                'body'     => $res->body(),
                'city_id'  => $cityId,
            ]);

            if (! $res->successful()) {
                throw new \RuntimeException('Gagal mengambil kecamatan dari RajaOngkir.');
            }

            $json = $res->json();

            return $json['data'] ?? [];
        });
    }

    /* --------------------------------------
     *  ONGKIR: /district/domestic-cost
     * ----------------------------------- */

    public function calculateDomesticCostByDistrict(int $originDistrictId, int $destinationDistrictId, int $weight): array
    {
        if ($this->useMock()) {
            return $this->mockCosts($originDistrictId, $destinationDistrictId, $weight);
        }

        $ttl = now()->addSeconds(config('rajaongkir.cache_ttl.cost', 60 * 30));
        $key = "rajaongkir:cost:{$originDistrictId}:{$destinationDistrictId}:{$weight}";

        return Cache::remember($key, $ttl, function () use ($originDistrictId, $destinationDistrictId, $weight) {
            $courier = (string) config(
                'rajaongkir.courier_codes',
                'jne:jnt'
            );

            $price = (string) config('rajaongkir.price', 'lowest');

            $payload = [
                'origin'      => $originDistrictId,
                'destination' => $destinationDistrictId,
                'weight'      => $weight,
                'courier'     => $courier,
                'price'       => $price,
            ];

            $res = $this->client()
                ->asForm() 
                ->post('/calculate/district/domestic-cost', $payload);

            Log::debug('RajaOngkir raw', [
                'endpoint'    => '/district/domestic-cost',
                'status'      => $res->status(),
                'body'        => $res->body(),
                'origin'      => $originDistrictId,
                'destination' => $destinationDistrictId,
                'weight'      => $weight,
                'payload'     => $payload,
            ]);

            if (! $res->successful()) {
                throw new \RuntimeException('Gagal menghitung ongkos kirim.');
            }

            $json = $res->json();

            // Sudah dalam format flat (name, code, service, description, cost, etd)
            return $json['data'] ?? [];
        });
    }

    /* --------------------------------------
     *  MOCK DATA (untuk RAJAONGKIR_ENABLED=false)
     * ----------------------------------- */

    protected function mockProvinces(): array
    {
        return [
            ['id' => 10, 'name' => 'DKI JAKARTA'],
            ['id' => 12, 'name' => 'JAWA TENGAH'],
        ];
    }

    protected function mockCities(int $provinceId): array
    {
        if ($provinceId === 10) {
            return [
                ['id' => 501, 'name' => 'JAKARTA SELATAN'],
                ['id' => 502, 'name' => 'JAKARTA PUSAT'],
            ];
        }

        return [
            ['id' => 601, 'name' => 'SEMARANG'],
            ['id' => 602, 'name' => 'SRAGEN'],
        ];
    }

    protected function mockDistricts(int $cityId): array
    {
        if ($cityId === 602) {
            return [
                ['id' => 7001, 'name' => 'MASARAN'],
                ['id' => 7002, 'name' => 'SRAGEN'],
            ];
        }

        return [
            ['id' => 8001, 'name' => 'KOTA BARU'],
        ];
    }

    protected function mockCosts(int $originDistrictId, int $destinationDistrictId, int $weight): array
    {
        // weight diabaikan, cuma contoh aja
        return [
            [
                'name'        => 'ID Express',
                'code'        => 'ide',
                'service'     => 'STD',
                'description' => 'Std',
                'cost'        => 14600,
                'etd'         => '0-0 day',
            ],
            [
                'name'        => 'J&T Express',
                'code'        => 'jnt',
                'service'     => 'EZ',
                'description' => 'Reguler',
                'cost'        => 19000,
                'etd'         => '',
            ],
            [
                'name'        => 'JNE',
                'code'        => 'jne',
                'service'     => 'REG',
                'description' => 'Layanan Reguler',
                'cost'        => 18000,
                'etd'         => '3 day',
            ],
        ];
    }
}
