<?php

namespace Samik\LaravelAdmin\Policies;

use Samik\LaravelAdmin\Models\Setting;
use Illuminate\Auth\Access\HandlesAuthorization;

class SettingPolicy extends CrudPolicy
{
    public function setValue(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }
}