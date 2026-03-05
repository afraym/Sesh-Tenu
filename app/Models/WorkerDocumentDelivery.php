<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerDocumentDelivery extends Model
{
    protected $fillable = [
        'worker_id',
        'year',
        'month',
        'shift',
        'morning_delivery_date',
        'evening_delivery_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'morning_delivery_date' => 'date',
        'evening_delivery_date' => 'date',
        'year' => 'integer',
        'month' => 'integer',
        'shift' => 'string',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
