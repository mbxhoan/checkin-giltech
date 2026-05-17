<?php

namespace App\Policies;

use App\Models\ImpExpFile;
use App\Models\User;

class ImpExpFilePolicy
{
    public function before(User $user)
    {
        if ($user->isSysAdmin()) {
            return true;
        }
    }

    public function view_progress(User $user, ImpExpFile $impExpFile): bool
    {
        return $user->authorizeObjectByEvent($impExpFile);
    }
}
