<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user is admin for all authorization.
     */
    public function before(User $user)
    {
        if ($user->isSysAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }
    }

    public function edit(User $user, User $userEdit): bool
    {
        return $user->event->company->id == $userEdit->company->id;
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $current_user, User $user): bool
    {
        return $current_user->id === $user->id;
    }
}
