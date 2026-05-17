<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiBasicAuthWebhookMiddleware
{
    use ApiResponser;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = env('WEBHOOK_AUTH_USER', 'your_username');
        $password = env('WEBHOOK_AUTH_PASS', 'your_password');

        if ($request->getUser() !== $username || $request->getPassword() !== $password) {
            // header('WWW-Authenticate: Basic realm="Webhook"');
            // abort(401, 'Unauthorized');
            // return response()->json('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);

            return $this->responseError('Unauthorized', 401);
        }

        return $next($request);
    }
}
