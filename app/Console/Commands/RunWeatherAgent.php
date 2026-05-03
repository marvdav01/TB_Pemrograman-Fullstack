<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WeatherAdapterAgent;

class RunWeatherAgent extends Command
{
    protected $signature = 'agent:run-weather {date? : Tanggal dalam format Y-m-d (default: hari ini)}';
    protected $description = 'Menjalankan Weather-Adaptive Guide Agent untuk tanggal tertentu';

    public function handle(WeatherAdapterAgent $agent)
    {
        $date = $this->argument('date') ?? now()->format('Y-m-d');

        $this->info("🌦️  Menjalankan Weather-Adaptive Agent untuk tanggal: {$date}");
        $this->newLine();

        $processed = $agent->executeAgentForDate($date);

        if (empty($processed)) {
            $this->warn('ℹ️  Tidak ada itinerary yang perlu diubah (tidak ada hujan, atau tidak ada jadwal).');
            return;
        }

        $this->info("✅ Agent selesai! " . count($processed) . " itinerary berhasil dialihkan:");
        $this->table(
            ['Itinerary ID', 'User', 'Kota', 'Dari', 'Ke'],
            collect($processed)->map(fn($p) => [
                $p['itinerary_id'],
                $p['user'],
                $p['city'],
                $p['old_destination'],
                $p['new_destination'],
            ])
        );
    }
}
