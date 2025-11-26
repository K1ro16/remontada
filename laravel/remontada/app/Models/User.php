<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_business_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
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

    // Relationships
    public function currentBusiness()
    {
        return $this->belongsTo(Business::class, 'current_business_id');
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'role_user')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withPivot('business_id')
                    ->withTimestamps();
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Helper methods
    public function hasRole($roleName, $businessId = null)
    {
        $businessId = $businessId ?? $this->current_business_id;
        
        return $this->roles()
                    ->where('roles.name', $roleName)
                    ->where('role_user.business_id', $businessId)
                    ->exists();
    }

    public function hasPermission($permissionName, $businessId = null)
    {
        $businessId = $businessId ?? $this->current_business_id;
        
        return $this->roles()
                    ->where('role_user.business_id', $businessId)
                    ->whereHas('permissions', function($query) use ($permissionName) {
                        $query->where('name', $permissionName);
                    })
                    ->exists();
    }
}
