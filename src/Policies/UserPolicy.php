<?php

namespace Samik\LaravelAdmin\Policies;

use Samik\LaravelAdmin\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy extends CrudPolicy
{
    public function editProfile(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }
}
