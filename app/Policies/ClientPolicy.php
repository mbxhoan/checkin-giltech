<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientPolicy
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
    public function edit(User $user, Client $client): bool
    {
        return $user->authorizeObjectByEvent($client);
    }
}
