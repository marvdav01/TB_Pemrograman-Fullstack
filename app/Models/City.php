<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name', 'country', 'latitude', 'longitude', 'openweather_city_name'];

    public function destinations()
    {
        return $this->hasMany(Destination::class);
    }

    public function weatherSimulations()
    {
        return $this->hasMany(WeatherSimulation::class);
    }
}
