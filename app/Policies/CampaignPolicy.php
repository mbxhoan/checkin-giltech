<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function before(User $user)
    {
        if ($user->isSysAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     */
    public function edit(User $user, Campaign $campaign): bool
    {
        return $user->authorizeObjectByEvent($campaign);
    }
    public function view_history(User $user, Campaign $campaign): bool
    {
        return $user->authorizeObjectByEvent($campaign);
    }
    public function create_campaign(User $user, Campaign $campaign): bool
    {
        return $user->authorizeObjectByEvent($campaign);
    }
    public function view_progress(User $user, Campaign $campaign): bool
    {
        return $user->authorizeObjectByEvent($campaign);
    }
    public function view_send_mail_table(User $user, Campaign $campaign): bool
    {
        return $user->authorizeObjectByEvent($campaign);
    }
    public function view_history_table(User $user, Campaign $campaign): bool
    {
        return $user->authorizeObjectByEvent($campaign);
    }

    /* EMAIL */
    public function export_report_error_email(User $user, Campaign $campaign): bool
    {
        return $user->authorizeObjectByEvent($campaign);
    }
}
