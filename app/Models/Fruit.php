<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fruit extends Model
{
    protected $guarded = [];

    public function truckFruits()
    {
        return $this->hasMany(TruckFruit::class);
    }
}
