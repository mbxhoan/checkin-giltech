<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Comment' => 'App\Policies\CommentPolicy',
        // 'App\Models\Post' => 'App\Policies\PostPolicy',
        // 'App\Models\User' => 'App\Policies\UserPolicy',
        // 'App\Models\Media' => 'App\Policies\MediaPolicy',

        // 'App\Models\Event' => 'App\Policies\EventPolicy',
        // \App\Models\Company::class => \App\Policies\CompanyPolicy::class,

        \App\Models\Event::class        => \App\Policies\EventPolicy::class,
        \App\Models\Client::class       => \App\Policies\ClientPolicy::class,
        \App\Models\Checkin::class      => \App\Policies\CheckinPolicy::class,
        \App\Models\Campaign::class     => \App\Policies\CampaignPolicy::class,
        \App\Models\Label::class        => \App\Policies\LabelPolicy::class,
        \App\Models\Card::class         => \App\Policies\CardPolicy::class,
        \App\Models\LuckyDraw::class    => \App\Policies\LuckyDrawPolicy::class,
        \App\Models\LandingPage::class  => \App\Policies\LandingPagePolicy::class,
        \App\Models\User::class         => \App\Policies\UserPolicy::class,
        \App\Models\Media::class        => \App\Policies\MediaPolicy::class,
        \App\Models\History::class      => \App\Policies\HistoryPolicy::class,
        \App\Models\Audio::class        => \App\Policies\AudioPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('viewLogViewer', fn (?User $user) => $user?->isSysAdmin() ?? false);
    }
}
