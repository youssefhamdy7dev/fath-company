<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
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
     * Get truck fruit records associated with this client.
     */
    public function truckFruit(): HasMany
    {
        return $this->hasMany(TruckFruit::class);
    }
}
