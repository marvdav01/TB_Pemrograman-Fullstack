<?php

namespace App\Events;

use App\Models\Itinerary;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItineraryAutoChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $itinerary;

    /**
     * Create a new event instance.
     */
    public function __construct(Itinerary $itinerary)
    {
        $this->itinerary = $itinerary->load('destination');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->itinerary->user_id),
        ];
    }

    /**
     * Data yang dikirimkan ke frontend.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => 'Hujan terdeteksi! Jadwal outdoor Anda telah dialihkan ke destinasi indoor otomatis: ' . $this->itinerary->destination->name,
            'itinerary_id' => $this->itinerary->id,
        ];
    }
}
