<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherApiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openweathermap.org/data/2.5';

    public function __construct()
    {
        $this->apiKey = config('services.openweather.key', '');
    }

    /**
     * Ambil cuaca saat ini dari OpenWeatherMap berdasarkan nama kota.
     * Return: 'sunny', 'cloudy', 'rainy', atau null jika gagal.
     */
    public function getCurrentCondition(string $cityName): ?string
    {
        if (empty($this->apiKey)) {
            return null; // Gunakan simulasi jika API key tidak tersedia
        }

        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/weather", [
                'q' => $cityName . ',ID',
                'appid' => $this->apiKey,
                'units' => 'metric',
            ]);

            if ($response->failed()) {
                Log::warning("OpenWeatherMap gagal untuk kota: {$cityName}");
                return null;
            }

            $data = $response->json();
            $weatherId = $data['weather'][0]['id'] ?? 800;

            // Mapping ID cuaca OpenWeatherMap ke kondisi kita
            // 2xx = Thunderstorm, 3xx = Drizzle, 5xx = Rain -> rainy
            // 8xx = Clear -> sunny, 7xx = Atmosphere, 801-804 = Clouds -> cloudy
            if ($weatherId >= 200 && $weatherId < 600) {
                return 'rainy';
            } elseif ($weatherId === 800) {
                return 'sunny';
            } else {
                return 'cloudy';
            }

        } catch (\Exception $e) {
            Log::error("Error OpenWeatherMap: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Ambil prakiraan cuaca 5 hari ke depan.
     * Return: array ['YYYY-MM-DD' => 'rainy'|'cloudy'|'sunny']
     */
    public function getForecast(string $cityName): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/forecast", [
                'q' => $cityName . ',ID',
                'appid' => $this->apiKey,
                'units' => 'metric',
            ]);

            if ($response->failed()) return [];

            $data = $response->json();
            $result = [];

            foreach ($data['list'] as $item) {
                $date = date('Y-m-d', $item['dt']);
                $weatherId = $item['weather'][0]['id'] ?? 800;

                if (!isset($result[$date])) {
                    if ($weatherId >= 200 && $weatherId < 600) {
                        $result[$date] = 'rainy';
                    } elseif ($weatherId === 800) {
                        $result[$date] = 'sunny';
                    } else {
                        $result[$date] = 'cloudy';
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Error forecast: " . $e->getMessage());
            return [];
        }
    }
}
