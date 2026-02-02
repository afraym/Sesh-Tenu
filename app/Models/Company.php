<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the workers for the company.
     */
    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }
}
