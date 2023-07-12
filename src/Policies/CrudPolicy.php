<?php

namespace Samik\LaravelAdmin\Policies;

use Samik\LaravelAdmin\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrudPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if($user->role->unrestricted) return true;
    }

    public function create(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }

    public function read(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }

    public function update(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }

    public function delete(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }

    public function import(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }

    public function export(User $user)
    {
        return is_policy_authorized($this, __FUNCTION__, $user->role_id);
    }
}
