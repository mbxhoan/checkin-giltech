<?php

namespace App\Policies;

use App\Models\Checkin;
use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CheckinPolicy
{
    public function before(User $user)
    {
        // dd($user);
        // if ($user->isSysAdmin()) {
        //     return true;
        // }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Checkin $checkin): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Checkin $checkin): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Checkin $checkin): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Checkin $checkin): bool
    {
        return false;
    }
}
