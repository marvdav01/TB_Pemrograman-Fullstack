<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItineraryLog extends Model
{
    protected $fillable = [
        'itinerary_id', 'user_id', 'old_destination',
        'new_destination', 'weather_condition', 'city_name', 'visit_date', 'reason'
    ];

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
