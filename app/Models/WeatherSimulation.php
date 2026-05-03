<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherSimulation extends Model
{
    protected $fillable = ['city_id', 'date', 'condition'];
}
