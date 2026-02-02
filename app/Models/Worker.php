<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Worker extends Model
{
    protected $fillable = [
        'project_name',
        'company_id',
        'worker_name',
        'mobile_number',
        'id_number',
        'job_type_id',
        'access_code',
    ];

    /**
     * Get the company that owns the worker.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the job type of the worker.
     */
    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class);
    }
}
