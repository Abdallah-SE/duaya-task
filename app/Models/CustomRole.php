<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class CustomRole extends SpatieRole
{
    /**
     * Get the role settings for this role.
     */
    public function roleSetting()
    {
        return $this->hasOne(RoleSetting::class);
    }
}

