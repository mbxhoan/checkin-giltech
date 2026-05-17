<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CardPolicy
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
    public function edit(User $user, Card $card): bool
    {
        return $user->authorizeObjectByEvent($card);
    }
    public function render_card(User $user, Card $card): bool
    {
        return $user->authorizeObjectByEvent($card);
    }
    public function view_progress(User $user, Card $card): bool
    {
        return $user->authorizeObjectByEvent($card);
    }
    public function download_images(User $user, Card $card): bool
    {
        return $user->authorizeObjectByEvent($card);
    }
    public function get_full_screen(User $user, Card $card): bool
    {
        return $user->authorizeObjectByEvent($card);
    }
}
