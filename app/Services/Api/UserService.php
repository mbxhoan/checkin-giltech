<?php
namespace App\Services\Api;

use App\Models\Client;
use App\Models\Event;
use App\Models\User;
use App\Services\BaseService;

class UserService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(User::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function language()
    {
        return app(LanguageService::class);
    }

    public function landing_page()
    {
        return app(LandingPageService::class);
    }

    public function validateApiUser(string $username, Event $event)
    {
        $user = $this->findByAttributes([
            'username' => $username
        ]);

        if (!$user) {
            return false;
        }

        if (in_array($user->status, [
            User::STATUS_ACTIVE,
            User::STATUS_INACTIVE
        ]) ) {
            if ($user->event_id == $event->id) {
                return true;
            }
        }

        return false;
    }
}
