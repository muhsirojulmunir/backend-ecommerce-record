<?php

namespace App\Services\Customer;

use Illuminate\Support\Facades\Http;
use App\Models\WebsiteSetting;

class ShippingService
{
    protected $apiKey;
    protected $baseUrl;
    protected $originCityId;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key') ?? env('RAJAONGKIR_API_KEY');
        $this->baseUrl = config('services.rajaongkir.base_url') ?? env('RAJAONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter');
        $this->originCityId = WebsiteSetting::get('store_city_id', env('STORE_CITY_ID', 152)); // default city id (Jakarta Pusat)
    }

    public function getProvinces()
    {
        $response = Http::withHeaders([
            'key' => $this->apiKey,
        ])->get("{$this->baseUrl}/province");

        if ($response->successful()) {
            return $response->json()['rajaongkir']['results'];
        }

        return [];
    }

    public function getCities($provinceId = null)
    {
        $url = "{$this->baseUrl}/city";
        if ($provinceId) {
            $url .= "?province={$provinceId}";
        }

        $response = Http::withHeaders([
            'key' => $this->apiKey,
        ])->get($url);

        if ($response->successful()) {
            return $response->json()['rajaongkir']['results'];
        }

        return [];
    }

    public function calculateCost(array $data)
    {
        $destination = $data['destination_city_id'];
        $weight = $data['weight_gram'] ?? 1000;
        $courier = $data['courier'] ?? 'jne'; // jne, pos, tiki

        $response = Http::withHeaders([
            'key' => $this->apiKey,
        ])->post("{$this->baseUrl}/cost", [
            'origin' => $this->originCityId,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier,
        ]);

        if ($response->successful()) {
            return $response->json()['rajaongkir']['results'][0]['costs'] ?? [];
        }

        return [];
    }
}
