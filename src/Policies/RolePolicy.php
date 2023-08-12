<?php

namespace Samik\LaravelAdmin\Policies;

use Samik\LaravelAdmin\Models\User;
use Samik\LaravelAdmin\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy extends CrudPolicy
{
    public function setPermissions(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }
}
