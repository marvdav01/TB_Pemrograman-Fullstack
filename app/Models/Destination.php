<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = ['name', 'city_id', 'type', 'category', 'latitude', 'longitude', 'description'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }
}
