<?php

namespace App\Policies;

use App\Models\LandingPage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LandingPagePolicy
{
    public function before(User $user)
    {
        if ($user->isSysAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function edit(User $user, LandingPage $landingPage): bool
    {
        return $user->authorizeObjectByEvent($landingPage);
    }
}
