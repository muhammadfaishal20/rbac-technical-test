<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The guard name for Spatie Permission
     * This ensures roles and permissions use 'web' guard
     */
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Override permissions relationship to always return empty
     * Since we don't use direct permissions, only via roles.
     * The table model_has_permissions exists but is always empty.
     */
    public function permissions(): BelongsToMany
    {
        // Use the standard Spatie relationship but it will always return empty
        // since we never insert data into model_has_permissions table
        $permissionClass = $this->getPermissionClass();
        
        return $this->morphToMany(
            $permissionClass,
            'model',
            config('permission.table_names.model_has_permissions'),
            config('permission.column_names.model_morph_key'),
            app(\Spatie\Permission\PermissionRegistrar::class)->pivotPermission
        );
    }

    /**
     * Override hasDirectPermission to always return false
     * This prevents Spatie from querying the non-existent model_has_permissions table
     */
    public function hasDirectPermission($permission): bool
    {
        // We don't use direct permissions, always return false
        // This prevents loadMissing('permissions') from being called
        return false;
    }

    /**
     * Override hasPermissionTo to only check via roles
     * This prevents Spatie from querying the non-existent model_has_permissions table
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        // Skip wildcard permission check if enabled
        if ($this->getWildcardClass()) {
            return $this->hasWildcardPermission($permission, $guardName);
        }

        $permission = $this->filterPermission($permission, $guardName);

        // Only check via roles, skip direct permissions
        return $this->hasPermissionViaRole($permission);
    }

    /**
     * Override getPermissionsAttribute to only return permissions via roles
     * We don't use direct permissions, only via roles
     */
    public function getPermissionsAttribute()
    {
        // Only return permissions via roles, not direct permissions
        return $this->getPermissionsViaRoles();
    }

    /**
     * Override getDirectPermissions to return empty collection
     * Since we don't use direct permissions
     */
    public function getDirectPermissions(): Collection
    {
        return collect([]);
    }
}
