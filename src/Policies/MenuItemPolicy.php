<?php

namespace Samik\LaravelAdmin\Policies;

use Samik\LaravelAdmin\Models\User;
use Samik\LaravelAdmin\Models\MenuItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuItemPolicy extends CrudPolicy
{
    public function reOrder(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }
}