<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWithdrawal extends Model
{
    protected $guarded = [];
    protected $casts = [
        'date' => 'date',
    ];
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
