<?php

namespace App\Policies;

use App\Models\LuckyDraw;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LuckyDrawPolicy
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
    public function edit(User $user, LuckyDraw $luckyDraw): bool
    {
        return $user->authorizeObjectByEvent($luckyDraw);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LuckyDraw $luckyDraw): bool
    {
        return $user->authorizeObjectByEvent($luckyDraw);
    }
}
