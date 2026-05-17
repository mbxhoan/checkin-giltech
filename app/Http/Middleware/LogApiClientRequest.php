<?php

namespace App\Http\Middleware;

use App\Models\ApiClientLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class LogApiClientRequest
{
    private const LOGGABLE_METHODS = [
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    private const LOGGABLE_PATHS = [
        'api/registrations/*',
        'api/payments/create',
        'api/payments/status/*',
        'api/orders/*',
        'api/orders/*/remove-promo',
        'api/portal/*',
        'api/payments/onepay/*',
        'api/onepay/*',
        'api/v1/clients/*',
        'api/v1/webhook/*',
        'api/page_access_logs/store',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!$this->shouldLog($request)) {
            return $next($request);
        }

        $startedAt = microtime(true);

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->storeLog($request, null, $exception, $startedAt);
            throw $exception;
        }

        $this->storeLog($request, $response, null, $startedAt);

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        return in_array(strtoupper($request->method()), self::LOGGABLE_METHODS, true)
            && $request->is(self::LOGGABLE_PATHS);
    }

    private function storeLog(Request $request, $response = null, ?Throwable $exception = null, float $startedAt = 0): void
    {
        $responseStatus = $response instanceof SymfonyResponse ? $response->getStatusCode() : null;
        $requestPayload = $this->sanitizePayload([
            'source' => $this->resolveSource($request),
            'ip' => $request->ip(),
            'route_name' => $request->route()?->getName(),
            'query' => $this->sanitizePayload($request->query()),
            'body' => $this->sanitizePayload($this->extractRequestBody($request)),
            'duration_ms' => $startedAt > 0 ? (int) round((microtime(true) - $startedAt) * 1000) : null,
        ]);

        $responsePayload = [
            'http_status' => $responseStatus ?? $this->resolveExceptionStatus($exception),
            'content_type' => $response instanceof SymfonyResponse ? $response->headers->get('Content-Type') : null,
            'body' => $this->sanitizeResponseBody($response),
        ];

        if ($exception) {
            $responsePayload['exception'] = class_basename($exception);
            $responsePayload['message'] = $exception->getMessage();
        }

        try {
            ApiClientLog::query()->create([
                'method' => strtoupper($request->method()),
                'endpoint' => '/' . ltrim($request->path(), '/'),
                'request' => $requestPayload,
                'response' => $responsePayload,
                'user_agent' => $request->userAgent(),
                'status' => $exception ? 'EXCEPTION' : ($responseStatus !== null && $responseStatus >= 400 ? 'ERROR' : 'SUCCESS'),
            ]);
        } catch (Throwable $logException) {
            // Logging must never break the business request path.
            Log::warning('API client request log persistence failed', [
                'method' => strtoupper($request->method()),
                'path' => '/' . ltrim($request->path(), '/'),
                'status' => $responsePayload['http_status'] ?? null,
                'error' => $logException->getMessage(),
            ]);
        }
    }

    private function extractRequestBody(Request $request): array
    {
        $payload = $request->all();

        if (!empty($request->allFiles())) {
            foreach ($request->allFiles() as $key => $file) {
                $payload[$key] = $file;
            }
        }

        return $payload;
    }

    private function sanitizeResponseBody($response)
    {
        if (!$response instanceof SymfonyResponse || !method_exists($response, 'getContent')) {
            return null;
        }

        $content = $response->getContent();

        if (is_string($content) && $content !== '') {
            $decoded = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->sanitizePayload($decoded);
            }

            return Str::limit($content, 5000, '...');
        }

        return $content;
    }

    private function resolveSource(Request $request): string
    {
        $path = Str::of('/' . ltrim($request->path(), '/'))->lower();

        if ($path->contains('onepay')) {
            return 'OnePay';
        }

        if ($path->contains(['webhook'])) {
            return 'Webhook';
        }

        if ($path->contains(['registrations', 'payments', 'portal', 'orders', 'clients'])) {
            return 'Web đăng ký';
        }

        return 'API ngoài';
    }

    private function resolveExceptionStatus(?Throwable $exception): int
    {
        if (!$exception) {
            return 500;
        }

        if ($exception instanceof ValidationException) {
            return 422;
        }

        if ($exception instanceof AuthenticationException) {
            return 401;
        }

        if ($exception instanceof AuthorizationException) {
            return 403;
        }

        if ($exception instanceof ModelNotFoundException) {
            return 404;
        }

        if ($exception instanceof HttpExceptionInterface) {
            return (int) $exception->getStatusCode();
        }

        return 500;
    }

    private function sanitizePayload($payload)
    {
        if ($payload instanceof UploadedFile) {
            return [
                'name' => $payload->getClientOriginalName(),
                'mime' => $payload->getClientMimeType(),
                'size' => $payload->getSize(),
            ];
        }

        if ($payload instanceof \Illuminate\Contracts\Support\Arrayable) {
            $payload = $payload->toArray();
        }

        if ($payload instanceof \JsonSerializable) {
            $payload = $payload->jsonSerialize();
        }

        if (is_object($payload)) {
            return get_class($payload);
        }

        if (!is_array($payload)) {
            if (is_string($payload)) {
                return Str::limit($payload, 5000, '...');
            }

            return $payload;
        }

        $redactedKeys = [
            'password',
            'current_password',
            'password_confirmation',
            'token',
            'login_token',
            'access_code',
            'secure_secret',
            'securehash',
            'vpc_securehash',
            'vpc_securehashtype',
            'authorization',
            'api_key',
            'secret',
            'hash',
        ];

        $sanitized = [];

        foreach ($payload as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (Arr::first($redactedKeys, fn ($needle) => Str::contains($normalizedKey, $needle))) {
                $sanitized[$key] = '[REDACTED]';
                continue;
            }

            $sanitized[$key] = $this->sanitizePayload($value);
        }

        return $sanitized;
    }
}
