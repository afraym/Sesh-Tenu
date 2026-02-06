<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'description',
        'address',
        'phone_number',
        'email',
        'logo',
    ];

    /**
     * Get the workers for the company.
     */
    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    /**
     * Get the equipment for the company.
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}
