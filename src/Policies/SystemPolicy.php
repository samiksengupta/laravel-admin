<?php

namespace Samik\LaravelAdmin\Policies;

use Samik\LaravelAdmin\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SystemPolicy extends CrudPolicy
{
    public function console(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }

    public function commands(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }
}
