<?php

namespace App\Models\Traits;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class);
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return false;
    }
}
