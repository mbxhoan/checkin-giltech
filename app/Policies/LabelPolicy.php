<?php

namespace App\Policies;

use App\Models\Label;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LabelPolicy
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
    public function edit(User $user, Label $label): bool
    {
        return $user->authorizeObjectByEvent($label);
    }
    public function render_label(User $user, Label $label): bool
    {
        return $user->authorizeObjectByEvent($label);
    }
}
