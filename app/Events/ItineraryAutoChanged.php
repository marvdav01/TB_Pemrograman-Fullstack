<?php

namespace App\Events;

use App\Models\Itinerary;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItineraryAutoChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $itinerary;

    public function __construct(Itinerary $itinerary)
    {
        $this->itinerary = $itinerary;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->itinerary->user_id),
        ];
    }

    public function broadcastWith(): array
    {
        $city = $this->itinerary->destination->city->name ?? 'kota Anda';
        $dest = $this->itinerary->destination->name ?? 'destinasi indoor';

        return [
            'message'        => "🌧️ Hujan terdeteksi di {$city}! Jadwal outdoor Anda telah dialihkan ke \"{$dest}\" secara otomatis.",
            'itinerary_id'   => $this->itinerary->id,
            'new_destination' => $dest,
            'city'           => $city,
        ];
    }
}
