<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Destination;
use App\Models\WeatherSimulation;
use App\Models\Itinerary;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TravelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat User Contoh
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Destinasi yang Beragam (Jakarta, Bandung, Bali)
        $cities = [
            1 => 'Jakarta',
            2 => 'Bandung',
            3 => 'Bali',
        ];

        $destinations = [
            // Jakarta
            ['name' => 'Monumen Nasional (Monas)', 'type' => 'outdoor', 'city_id' => 1],
            ['name' => 'Taman Mini Indonesia Indah', 'type' => 'outdoor', 'city_id' => 1],
            ['name' => 'Museum Nasional', 'type' => 'indoor', 'city_id' => 1],
            ['name' => 'Sea World Ancol', 'type' => 'indoor', 'city_id' => 1],
            
            // Bandung
            ['name' => 'Tangkuban Perahu', 'type' => 'outdoor', 'city_id' => 2],
            ['name' => 'Kawah Putih', 'type' => 'outdoor', 'city_id' => 2],
            ['name' => 'Orchid Forest Cikole', 'type' => 'outdoor', 'city_id' => 2],
            ['name' => 'Museum Geologi', 'type' => 'indoor', 'city_id' => 2],
            ['name' => 'Trans Studio Bandung', 'type' => 'indoor', 'city_id' => 2],
            
            // Bali
            ['name' => 'Pantai Kuta', 'type' => 'outdoor', 'city_id' => 3],
            ['name' => 'Pura Tanah Lot', 'type' => 'outdoor', 'city_id' => 3],
            ['name' => 'Garuda Wisnu Kencana', 'type' => 'outdoor', 'city_id' => 3],
            ['name' => 'Museum Pasifika', 'type' => 'indoor', 'city_id' => 3],
            ['name' => 'Upside Down World Bali', 'type' => 'indoor', 'city_id' => 3],
        ];

        foreach ($destinations as $dest) {
            Destination::updateOrCreate(['name' => $dest['name']], $dest);
        }

        // 3. Simulasi Cuaca (7 Hari ke Depan)
        $conditions = ['sunny', 'cloudy', 'rainy'];
        foreach ($cities as $cityId => $cityName) {
            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::now()->addDays($i)->format('Y-m-d');
                WeatherSimulation::updateOrCreate(
                    ['city_id' => $cityId, 'date' => $date],
                    ['condition' => $conditions[array_rand($conditions)]]
                );
            }
        }

        // Pastikan ada satu yang pasti hujan besok untuk demo di Jakarta
        WeatherSimulation::updateOrCreate(
            ['city_id' => 1, 'date' => Carbon::tomorrow()->format('Y-m-d')],
            ['condition' => 'rainy']
        );

        // 4. Itinerary Awal untuk User
        $itineraries = [
            [
                'destination_id' => Destination::where('name', 'Monumen Nasional (Monas)')->first()->id,
                'visit_date' => Carbon::tomorrow()->format('Y-m-d'),
                'status' => 'planned'
            ],
            [
                'destination_id' => Destination::where('name', 'Pantai Kuta')->first()->id,
                'visit_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'status' => 'planned'
            ],
            [
                'destination_id' => Destination::where('name', 'Museum Geologi')->first()->id,
                'visit_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'status' => 'planned'
            ],
        ];

        foreach ($itineraries as $itinerary) {
            Itinerary::create(array_merge($itinerary, ['user_id' => $user->id]));
        }
    }
}
