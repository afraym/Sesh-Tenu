<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipment extends Model
{
    protected $fillable = [
        'project_name',
        'company_id',
        'previous_driver',
        'current_driver',
        'equipment_type',
        'model_year',
        'equipment_code',
        'equipment_number',
    ];

    /**
     * Get the company that owns the equipment.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
