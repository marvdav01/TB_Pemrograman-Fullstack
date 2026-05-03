<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WeatherAdapterAgent;

class RunWeatherAgent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:run-weather {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan Weather-Adaptive Guide Agent untuk tanggal tertentu';

    /**
     * Execute the console command.
     */
    public function handle(WeatherAdapterAgent $agent)
    {
        $date = $this->argument('date');
        $this->info("Menjalankan agent untuk tanggal: {$date}...");
        
        $agent->executeAgentForDate($date);
        
        $this->info("Agent selesai dijalankan.");
    }
}
