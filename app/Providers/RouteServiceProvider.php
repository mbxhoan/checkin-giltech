<?php

namespace App\Providers;

use App\Models\Media;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to your application's "home" route.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        parent::boot();

        RateLimiter::for('api', function (Request $request) {
            $key = $this->resolveRateKey($request, 'api');

            return [
                $this->buildPerMinuteLimit(
                    max(60, (int) config('api_security.rate_limits.api_per_minute', 600)),
                    $key,
                    $request,
                    'api'
                ),
                $this->buildPerSecondLimit(
                    max(5, (int) config('api_security.rate_limits.api_per_second', 40)),
                    $key,
                    $request,
                    'api'
                ),
            ];
        });

        RateLimiter::for('registration-submit', function (Request $request) {
            $key = $this->resolveRateKey($request, 'registration', true);

            return [
                $this->buildPerMinuteLimit(
                    max(30, (int) config('api_security.rate_limits.registration_per_minute', 120)),
                    $key,
                    $request,
                    'registration-submit'
                ),
                $this->buildPerSecondLimit(
                    max(2, (int) config('api_security.rate_limits.registration_per_second', 8)),
                    $key,
                    $request,
                    'registration-submit'
                ),
            ];
        });

        RateLimiter::for('registration-upload', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));
            $eventId = (string) $request->input('event_id', '0');
            $key = 'registration-upload:' . sha1($request->ip() . '|' . $email . '|' . $eventId);

            return [
                $this->buildPerMinuteLimit(
                    max(10, (int) config('api_security.rate_limits.registration_upload_per_minute', 40)),
                    $key,
                    $request,
                    'registration-upload'
                ),
                $this->buildPerSecondLimit(
                    max(1, (int) config('api_security.rate_limits.registration_upload_per_second', 4)),
                    $key,
                    $request,
                    'registration-upload'
                ),
            ];
        });

        RateLimiter::for('portal-login', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));
            $eventId = (string) $request->input('event_id', '0');
            $key = 'portal-login:' . sha1($request->ip() . '|' . $email . '|' . $eventId);

            return [
                $this->buildPerMinuteLimit(
                    max(5, (int) config('api_security.rate_limits.portal_login_per_minute', 20)),
                    $key,
                    $request,
                    'portal-login'
                ),
                $this->buildPerSecondLimit(
                    max(1, (int) config('api_security.rate_limits.portal_login_per_second', 10)),
                    $key,
                    $request,
                    'portal-login'
                ),
            ];
        });

        RateLimiter::for('client-upsert', function (Request $request) {
            $key = $this->resolveRateKey($request, 'client-upsert', true);

            return [
                $this->buildPerMinuteLimit(
                    max(30, (int) config('api_security.rate_limits.client_upsert_per_minute', 180)),
                    $key,
                    $request,
                    'client-upsert'
                ),
                $this->buildPerSecondLimit(
                    max(2, (int) config('api_security.rate_limits.client_upsert_per_second', 15)),
                    $key,
                    $request,
                    'client-upsert'
                ),
            ];
        });

        RateLimiter::for('onepay-callback', function (Request $request) {
            $key = 'onepay-callback:' . sha1($request->ip());

            return [
                $this->buildPerMinuteLimit(
                    max(60, (int) config('api_security.rate_limits.onepay_callback_per_minute', 360)),
                    $key,
                    $request,
                    'onepay-callback'
                ),
                $this->buildPerSecondLimit(
                    max(5, (int) config('api_security.rate_limits.onepay_callback_per_second', 30)),
                    $key,
                    $request,
                    'onepay-callback'
                ),
            ];
        });

        RateLimiter::for('onepay-querydr', function (Request $request) {
            $key = $this->resolveRateKey($request, 'onepay-querydr', true);

            return [
                $this->buildPerMinuteLimit(
                    max(10, (int) config('api_security.rate_limits.onepay_querydr_per_minute', 60)),
                    $key,
                    $request,
                    'onepay-querydr'
                ),
                $this->buildPerSecondLimit(
                    max(1, (int) config('api_security.rate_limits.onepay_querydr_per_second', 8)),
                    $key,
                    $request,
                    'onepay-querydr'
                ),
            ];
        });

        RateLimiter::for('webhook-inbound', function (Request $request) {
            $key = 'webhook:' . sha1($request->ip() . '|' . ($request->userAgent() ?: 'na'));

            return [
                $this->buildPerMinuteLimit(
                    max(20, (int) config('api_security.rate_limits.webhook_per_minute', 120)),
                    $key,
                    $request,
                    'webhook-inbound'
                ),
                $this->buildPerSecondLimit(
                    max(1, (int) config('api_security.rate_limits.webhook_per_second', 10)),
                    $key,
                    $request,
                    'webhook-inbound'
                ),
            ];
        });

        Route::model('medium', Media::class);
    }

    private function resolveRateKey(Request $request, string $namespace, bool $includePath = false): string
    {
        $principal = (string) (
            $request->user()?->id
            ?? $request->bearerToken()
            ?? $request->getUser()
            ?? $request->ip()
            ?? 'anonymous'
        );

        $path = $includePath ? ('|' . ltrim($request->path(), '/')) : '';

        return $namespace . ':' . sha1($principal . $path);
    }

    private function buildPerMinuteLimit(int $maxAttempts, string $key, Request $request, string $limiter): Limit
    {
        return Limit::perMinute($maxAttempts)
            ->by($key)
            ->response(fn () => $this->buildTooManyRequestsResponse($request, $limiter));
    }

    private function buildPerSecondLimit(int $maxAttempts, string $key, Request $request, string $limiter): Limit
    {
        return Limit::perSecond($maxAttempts)
            ->by($key . ':burst')
            ->response(fn () => $this->buildTooManyRequestsResponse($request, $limiter));
    }

    private function buildTooManyRequestsResponse(Request $request, string $limiter)
    {
        return response()->json([
            'message' => 'Too Many Requests',
            'error' => 'rate_limited',
            'limiter' => $limiter,
            'path' => '/' . ltrim($request->path(), '/'),
        ], 429);
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        Route::domain(config('app.scan_domain'))
            ->middleware('web')
            ->as('scan.')
            ->group(base_path('routes/scan.php'));

        $this->mapApiRoutes();
        $this->mapAuthRoutes();
        $this->mapWebRoutes();
        $this->mapAdminRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        // Route::domain(config('app.frontend_domain'))
        //     ->middleware('web')
        //     ->group(base_path('routes/web.php'));

    }

    protected function mapWebScanRoutes(): void
    {
        // Đã chuyển lên trên
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "admin" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapAdminRoutes(): void
    {
        Route::prefix('admin')
            ->middleware([
                'web',
                'auth',
                // 'role:admin',
                'verified'
            ])
            ->as('admin.')
            ->group(base_path('routes/admin.php'));
    }

    /**
     * Define the "auth" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapAuthRoutes(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/auth.php'));
    }
}
