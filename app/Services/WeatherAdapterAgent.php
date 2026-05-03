<?php

namespace App\Services;

use App\Models\Itinerary;
use App\Models\Destination;
use App\Models\WeatherSimulation;
use App\Models\ItineraryLog;
use App\Events\ItineraryAutoChanged;
use Illuminate\Support\Facades\Log;

class WeatherAdapterAgent
{
    protected WeatherApiService $weatherApi;

    public function __construct(WeatherApiService $weatherApi)
    {
        $this->weatherApi = $weatherApi;
    }

    /**
     * Jalankan agent untuk semua itinerary 'outdoor' & 'planned' pada tanggal tertentu.
     * Logic: Rule-Based (IF-ELSE) murni, tanpa AI eksternal.
     */
    public function executeAgentForDate($date): array
    {
        $processed = [];

        // 1. Ambil semua itinerary outdoor yang masih planned pada tanggal tersebut
        $itineraries = Itinerary::where('visit_date', $date)
            ->where('status', 'planned')
            ->whereHas('destination', fn($q) => $q->where('type', 'outdoor'))
            ->with(['destination.city', 'user'])
            ->get();

        Log::info("WeatherAdapterAgent: memproses {$itineraries->count()} itinerary pada {$date}");

        foreach ($itineraries as $itinerary) {
            $city = $itinerary->destination->city;
            if (!$city) continue;

            // 2. Cek cuaca dari API real terlebih dahulu, fallback ke simulasi
            $condition = $this->resolveWeatherCondition($city, $date);

            Log::info("WeatherAdapterAgent: Kota {$city->name} - Kondisi: {$condition}");

            // 3. RULE: Jika HUJAN → cari destinasi indoor pengganti di kota yang sama
            if ($condition === 'rainy') {
                $oldDestination = $itinerary->destination;

                // Prioritaskan kategori yang sama (jika outdoor = gunung → cari indoor = museum)
                $indoorAlternative = $this->findBestIndoorAlternative(
                    $city->id,
                    $oldDestination->category
                );

                if ($indoorAlternative) {
                    // 4. Update itinerary
                    $itinerary->update([
                        'destination_id' => $indoorAlternative->id,
                        'status'         => 'auto_changed',
                        'notes'          => "Dialihkan otomatis: hujan terdeteksi di {$city->name} pada {$date}.",
                    ]);

                    // 5. Catat ke audit log
                    ItineraryLog::create([
                        'itinerary_id'    => $itinerary->id,
                        'user_id'         => $itinerary->user_id,
                        'old_destination' => $oldDestination->name,
                        'new_destination' => $indoorAlternative->name,
                        'weather_condition' => $condition,
                        'city_name'       => $city->name,
                        'visit_date'      => $date,
                        'reason'          => "Cuaca hujan terdeteksi di {$city->name}. Jadwal outdoor dialihkan ke destinasi indoor secara otomatis.",
                    ]);

                    // 6. Broadcast real-time ke user via Reverb
                    event(new ItineraryAutoChanged($itinerary->fresh(['destination.city'])));

                    $processed[] = [
                        'itinerary_id'    => $itinerary->id,
                        'user'            => $itinerary->user->name,
                        'old_destination' => $oldDestination->name,
                        'new_destination' => $indoorAlternative->name,
                        'city'            => $city->name,
                    ];
                }
            }
        }

        return $processed;
    }

    /**
     * Ambil kondisi cuaca: coba dari API real, fallback ke simulasi database.
     */
    protected function resolveWeatherCondition($city, string $date): string
    {
        // Coba ambil dari OpenWeatherMap API
        if ($city->openweather_city_name) {
            $apiCondition = $this->weatherApi->getCurrentCondition($city->openweather_city_name);
            if ($apiCondition !== null) {
                return $apiCondition;
            }
        }

        // Fallback ke tabel simulasi database
        $simulation = WeatherSimulation::where('city_id', $city->id)
            ->where('date', $date)
            ->first();

        return $simulation?->condition ?? 'sunny';
    }

    /**
     * Cari destinasi indoor terbaik berdasarkan kota dan kategori yang sama.
     */
    protected function findBestIndoorAlternative(int $cityId, string $category): ?Destination
    {
        // Prioritas 1: Indoor dengan kategori yang sama
        $alternative = Destination::where('city_id', $cityId)
            ->where('type', 'indoor')
            ->where('category', $category)
            ->inRandomOrder()
            ->first();

        // Prioritas 2: Indoor mana saja di kota yang sama
        if (!$alternative) {
            $alternative = Destination::where('city_id', $cityId)
                ->where('type', 'indoor')
                ->inRandomOrder()
                ->first();
        }

        return $alternative;
    }
}
