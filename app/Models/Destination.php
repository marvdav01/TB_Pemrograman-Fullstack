<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = ['name', 'city_id', 'type'];

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }
}
