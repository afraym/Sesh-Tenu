<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Worker extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'entity',
        'job_type_id',
        'national_id',
        'phone_number',
        'has_housing',
        'is_local_community',
        'address',
        'join_date',
        'end_date',
        'is_on_company_payroll',
        'salary',
    ];

    protected $casts = [
        'has_housing' => 'boolean',
        'is_local_community' => 'boolean',
        'is_on_company_payroll' => 'boolean',
        'join_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal:2',
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
