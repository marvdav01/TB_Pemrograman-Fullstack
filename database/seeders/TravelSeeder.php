<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\City;
use App\Models\Destination;
use App\Models\WeatherSimulation;
use App\Models\Itinerary;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TravelSeeder extends Seeder
{
    public function run(): void
    {
        // ============================
        // 1. BUAT USER CONTOH
        // ============================
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Budi Santoso', 'password' => Hash::make('password')]
        );

        // ============================
        // 2. BUAT DATA KOTA
        // ============================
        $cities = [
            ['name' => 'Jakarta',  'latitude' => -6.2088, 'longitude' => 106.8456, 'openweather_city_name' => 'Jakarta'],
            ['name' => 'Bandung',  'latitude' => -6.9175, 'longitude' => 107.6191, 'openweather_city_name' => 'Bandung'],
            ['name' => 'Bali',     'latitude' => -8.4095, 'longitude' => 115.1889, 'openweather_city_name' => 'Denpasar'],
            ['name' => 'Yogyakarta', 'latitude' => -7.7972, 'longitude' => 110.3688, 'openweather_city_name' => 'Yogyakarta'],
        ];

        foreach ($cities as $c) {
            City::updateOrCreate(['name' => $c['name']], $c);
        }

        $jakarta  = City::where('name', 'Jakarta')->first();
        $bandung  = City::where('name', 'Bandung')->first();
        $bali     = City::where('name', 'Bali')->first();
        $jogja    = City::where('name', 'Yogyakarta')->first();

        // ============================
        // 3. BUAT DATA DESTINASI (Outdoor & Indoor)
        // ============================
        $destinations = [
            // --- Jakarta ---
            ['name' => 'Monumen Nasional (Monas)',   'city_id' => $jakarta->id, 'type' => 'outdoor', 'category' => 'sejarah',  'latitude' => -6.1754, 'longitude' => 106.8272, 'description' => 'Ikon pusat kota Jakarta, monumen bersejarah yang megah.'],
            ['name' => 'Taman Mini Indonesia Indah', 'city_id' => $jakarta->id, 'type' => 'outdoor', 'category' => 'budaya',   'latitude' => -6.3024, 'longitude' => 106.8952, 'description' => 'Miniatur kebudayaan seluruh nusantara dalam satu taman.'],
            ['name' => 'Ancol Dreamland',             'city_id' => $jakarta->id, 'type' => 'outdoor', 'category' => 'hiburan',  'latitude' => -6.1274, 'longitude' => 106.8365, 'description' => 'Kawasan wisata pesisir dengan berbagai wahana seru.'],
            ['name' => 'Museum Nasional',             'city_id' => $jakarta->id, 'type' => 'indoor',  'category' => 'sejarah',  'latitude' => -6.1764, 'longitude' => 106.8219, 'description' => 'Koleksi artefak sejarah dan budaya terlengkap di Indonesia.'],
            ['name' => 'Galeri Nasional Indonesia',   'city_id' => $jakarta->id, 'type' => 'indoor',  'category' => 'budaya',   'latitude' => -6.1717, 'longitude' => 106.8305, 'description' => 'Pameran karya seni terbaik seniman Indonesia.'],
            ['name' => 'Sea World Ancol',             'city_id' => $jakarta->id, 'type' => 'indoor',  'category' => 'hiburan',  'latitude' => -6.1254, 'longitude' => 106.8348, 'description' => 'Akuarium raksasa dengan ribuan spesies biota laut.'],

            // --- Bandung ---
            ['name' => 'Tangkuban Perahu',   'city_id' => $bandung->id, 'type' => 'outdoor', 'category' => 'alam',     'latitude' => -6.7670, 'longitude' => 107.6098, 'description' => 'Kawah vulkanik aktif dengan pemandangan alam yang menakjubkan.'],
            ['name' => 'Kawah Putih',         'city_id' => $bandung->id, 'type' => 'outdoor', 'category' => 'alam',     'latitude' => -7.1669, 'longitude' => 107.4025, 'description' => 'Danau kawah dengan air berwarna putih kehijauan yang eksotis.'],
            ['name' => 'Orchid Forest Cikole','city_id' => $bandung->id, 'type' => 'outdoor', 'category' => 'alam',     'latitude' => -6.7403, 'longitude' => 107.5954, 'description' => 'Taman anggrek di tengah hutan pinus yang sejuk.'],
            ['name' => 'Museum Geologi',      'city_id' => $bandung->id, 'type' => 'indoor',  'category' => 'alam',     'latitude' => -6.9032, 'longitude' => 107.6126, 'description' => 'Koleksi batuan, fosil, dan peta geologi Indonesia.'],
            ['name' => 'Trans Studio Bandung','city_id' => $bandung->id, 'type' => 'indoor',  'category' => 'hiburan',  'latitude' => -6.9256, 'longitude' => 107.6386, 'description' => 'Theme park indoor terbesar di Asia Tenggara.'],

            // --- Bali ---
            ['name' => 'Pantai Kuta',         'city_id' => $bali->id, 'type' => 'outdoor', 'category' => 'pantai',   'latitude' => -8.7184, 'longitude' => 115.1686, 'description' => 'Pantai ikonik Bali dengan sunset yang memukau.'],
            ['name' => 'Pura Tanah Lot',       'city_id' => $bali->id, 'type' => 'outdoor', 'category' => 'budaya',   'latitude' => -8.6211, 'longitude' => 115.0869, 'description' => 'Pura Hindu di atas batu karang di tengah laut.'],
            ['name' => 'Garuda Wisnu Kencana', 'city_id' => $bali->id, 'type' => 'outdoor', 'category' => 'budaya',   'latitude' => -8.8101, 'longitude' => 115.1673, 'description' => 'Taman budaya dengan patung GWK setinggi 121 meter.'],
            ['name' => 'Museum Pasifika',      'city_id' => $bali->id, 'type' => 'indoor',  'category' => 'budaya',   'latitude' => -8.7965, 'longitude' => 115.1642, 'description' => 'Koleksi seni dari seluruh kawasan Pasifik.'],
            ['name' => 'Upside Down World Bali','city_id' => $bali->id, 'type' => 'indoor', 'category' => 'hiburan',  'latitude' => -8.8041, 'longitude' => 115.1752, 'description' => 'Atraksi foto interaktif yang unik dan kreatif.'],

            // --- Yogyakarta ---
            ['name' => 'Candi Borobudur',       'city_id' => $jogja->id, 'type' => 'outdoor', 'category' => 'sejarah',  'latitude' => -7.6079, 'longitude' => 110.2038, 'description' => 'Candi Buddha terbesar di dunia, warisan UNESCO.'],
            ['name' => 'Candi Prambanan',        'city_id' => $jogja->id, 'type' => 'outdoor', 'category' => 'sejarah',  'latitude' => -7.7520, 'longitude' => 110.4914, 'description' => 'Kompleks candi Hindu megah dari abad ke-9.'],
            ['name' => 'Pantai Parangtritis',    'city_id' => $jogja->id, 'type' => 'outdoor', 'category' => 'pantai',   'latitude' => -8.0257, 'longitude' => 110.3326, 'description' => 'Pantai selatan misterius dengan legenda Ratu Kidul.'],
            ['name' => 'Museum Ullen Sentalu',   'city_id' => $jogja->id, 'type' => 'indoor',  'category' => 'sejarah',  'latitude' => -7.5826, 'longitude' => 110.4215, 'description' => 'Museum budaya Jawa dan kesenian Keraton.'],
            ['name' => 'Jogja National Museum',  'city_id' => $jogja->id, 'type' => 'indoor',  'category' => 'sejarah',  'latitude' => -7.7991, 'longitude' => 110.3671, 'description' => 'Museum seni kontemporer bergaya modern.'],
        ];

        foreach ($destinations as $dest) {
            Destination::updateOrCreate(['name' => $dest['name']], $dest);
        }

        // ============================
        // 4. SIMULASI CUACA (7 HARI KE DEPAN)
        // ============================
        $allCities  = City::all();
        $conditions = ['sunny', 'cloudy', 'rainy'];

        foreach ($allCities as $city) {
            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::now()->addDays($i)->format('Y-m-d');
                WeatherSimulation::updateOrCreate(
                    ['city_id' => $city->id, 'date' => $date],
                    ['condition' => $conditions[array_rand($conditions)]]
                );
            }
        }

        // Pastikan besok Jakarta HUJAN (untuk demo)
        WeatherSimulation::updateOrCreate(
            ['city_id' => $jakarta->id, 'date' => Carbon::tomorrow()->format('Y-m-d')],
            ['condition' => 'rainy']
        );

        // ============================
        // 5. BUAT ITINERARY CONTOH
        // ============================
        $itineraries = [
            ['destination' => 'Monumen Nasional (Monas)',  'date' => Carbon::tomorrow()->format('Y-m-d')],
            ['destination' => 'Tangkuban Perahu',          'date' => Carbon::now()->addDays(2)->format('Y-m-d')],
            ['destination' => 'Pantai Kuta',               'date' => Carbon::now()->addDays(3)->format('Y-m-d')],
            ['destination' => 'Candi Borobudur',           'date' => Carbon::now()->addDays(4)->format('Y-m-d')],
            ['destination' => 'Garuda Wisnu Kencana',      'date' => Carbon::now()->addDays(5)->format('Y-m-d')],
        ];

        foreach ($itineraries as $item) {
            $dest = Destination::where('name', $item['destination'])->first();
            if ($dest) {
                Itinerary::create([
                    'user_id'        => $user->id,
                    'destination_id' => $dest->id,
                    'visit_date'     => $item['date'],
                    'status'         => 'planned',
                ]);
            }
        }

        $this->command->info('✅ Seeder selesai! Data siap digunakan.');
    }
}
