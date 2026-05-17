<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Blade::directive('humanize_date', function (string $params) {
            return "<?php echo humanize_date($params); ?>";
        });

        $defaultImageDriver = 'gd';
        config([
            'image.driver' => env('IMAGE_DRIVER', $defaultImageDriver),
            'media-library.image_driver' => env('IMAGE_DRIVER', $defaultImageDriver),
        ]);

        // view()->composer('layouts.sidebar', SidebarMenuComposer::class);
    }
}
