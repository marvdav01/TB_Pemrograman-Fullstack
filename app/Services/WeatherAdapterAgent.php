<?php

namespace App\Services;

use App\Models\Itinerary;
use App\Models\Destination;
use App\Models\WeatherSimulation;
use App\Events\ItineraryAutoChanged;

class WeatherAdapterAgent
{
    /**
     * Mengambil semua jadwal 'outdoor' dan 'planned' untuk tanggal tertentu.
     * Jika cuaca hujan, pindahkan ke destinasi 'indoor' di kota yang sama.
     */
    public function executeAgentForDate($date)
    {
        // 1. Ambil semua itinerary outdoor yang statusnya planned pada tanggal tersebut
        $itineraries = Itinerary::where('visit_date', $date)
            ->where('status', 'planned')
            ->whereHas('destination', function ($query) {
                $query->where('type', 'outdoor');
            })
            ->with('destination')
            ->get();

        foreach ($itineraries as $itinerary) {
            $cityId = $itinerary->destination->city_id;

            // 2. Cek simulasi cuaca untuk kota dan tanggal tersebut
            $weather = WeatherSimulation::where('city_id', $cityId)
                ->where('date', $date)
                ->first();

            // 3. Jika kondisi hujan, cari alternatif indoor di kota yang sama
            if ($weather && $weather->condition === 'rainy') {
                $indoorAlternative = Destination::where('city_id', $cityId)
                    ->where('type', 'indoor')
                    ->inRandomOrder()
                    ->first();

                if ($indoorAlternative) {
                    // 4. Update itinerary ke destinasi indoor dan ubah status
                    $itinerary->update([
                        'destination_id' => $indoorAlternative->id,
                        'status' => 'auto_changed'
                    ]);

                    // 5. Broadcast event real-time
                    event(new ItineraryAutoChanged($itinerary));
                }
            }
        }
    }
}
