<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the company that the user belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if the user is a company owner.
     */
    public function isCompanyOwner(): bool
    {
        return $this->role === 'company_owner';
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is an employee.
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Check if the user can manage all resources (super admin or admin).
     */
    public function canManageAll(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can access subscription-gated areas.
     */
    public function canManageSubscription(): bool
    {
        return $this->canManageAll() || $this->isCompanyOwner();
    }

    /**
     * Determine whether the user should be blocked by subscription checks.
     */
    public function requiresSubscription(): bool
    {
        return !$this->canManageAll();
    }

    /**
     * Determine whether the user's company has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return (bool) ($this->company?->hasActiveSubscription());
    }
}
