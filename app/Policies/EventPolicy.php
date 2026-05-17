<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function before(User $user)
    {
        if ($user->isSysAdmin()) {
            return true;
        }
    }

    public function edit(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* EVENT FILES */

    /* CHECKIN */
    public function config_checkin(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
    public function render_background_checkin(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
    public function export_report_checkin(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* CLIENT */
    public function list_client(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
    public function create_client(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
    public function get_data_client(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
    public function import_client(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
    public function export_template_import_client(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
    public function export_list_client(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
    public function fill_qrcode(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
    public function get_template_qrcodes(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
    public function download_qrcode_images(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* CHECKIN */
    public function list_checkin(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* CAMPAIGN */
    public function create_campaign(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* EMAIL */
    public function export_report_email(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* LABEL */
    public function create_label(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* CARD */
    public function create_card(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
    public function render_background_card(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* LANDING PAGE */
    public function create_landing_page(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* LANGUAGE DEFINES */
    public function generate_lang(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* LUCKY DRAWS */
    public function create_lucky_draw(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* REPORT */
    public function report(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }

    /* AUDIO */
    public function set_to_event_audio(User $user, Event $event): bool
    {
        return $user->authorizeSelfByEvent($event);
    }
}
