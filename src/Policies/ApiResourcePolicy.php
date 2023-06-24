<?php

namespace Samik\LaravelAdmin\Policies;

use Samik\LaravelAdmin\Models\ApiResource;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApiResourcePolicy extends CrudPolicy
{
    public function test(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }
}
