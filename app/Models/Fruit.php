<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fruit extends Model
{
    /**
     * Attributes available for mass assignment.
     */
    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get truck fruit records associated with this fruit.
     */
    public function truckFruits(): HasMany
    {
        return $this->hasMany(TruckFruit::class);
    }
}
