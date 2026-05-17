<?php

namespace App\HttpClient;

use App\Helpers\Helper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class HttpClient
{
    protected $headers;
    protected $url;
    protected $token = "";

    public function __construct($url, $headers = [
        'Content-Type' => 'application/json'
    ])
    {
        $this->url = $url;
        $this->headers = $headers;
    }

    public function getToken()
    {
        $token = Cookie::get('api_license_token');
        return $token;
        // return session('api_token');
        // return $this->token;
    }

    /* Call API Login to get token and return body json */
    public function login($endpoint, $body = [], $keyOfToken = 'token')
    {
        $response = Http::withHeaders($this->headers)->post("{$this->url}/{$endpoint}", $body);
        Cookie::queue('api_license_username', $body['username']);
        Cookie::queue('api_license_passkey', Helper::encryptData(env('API_LICENSE_SECRET_KEY'), $body['password']));

        if ($response->failed()) {
            return null;
        }

        $response = $response->json();

        if ($response['success']) {
            $token = $response['data'][$keyOfToken];
            $expireTime = $response['data']["token_expiration"];
            Cookie::queue('api_license_token', $token, 60 * 24 * 7);
            Cookie::queue('api_license_expire_time', $expireTime, 60*24*7);
            return $token;

            // session(['api_token' => $response['data'][$keyOfToken]]);
            // Cache::put('api_token', $token, now()->addDays(7));
            // Session::put('api_token', $token);
        }

        return $response;
    }

    public function get($endpoint, $parameters = [], $token = null)
    {
        if (!$token) {
            $token = $this->getToken();
        }

        $fullUrl = $this->url;

        if (!empty($endpoint)) {
            $fullUrl = "{$this->url}/{$endpoint}";
        }

        Log::info("HTTP REQUESTING /GET {$fullUrl}, params: ".json_encode($parameters));

        $response = Http::withHeaders($this->headers)
            ->withToken($this->getToken())
            ->get($fullUrl, $parameters);

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }

    public function post($endpoint = null, $body = [], $method = "post", $token = null, $noToken = true)
    {
        if (!$token) {
            $token = $this->getToken();
        }

        $fullUrl = $this->url;

        if (!empty($endpoint)) {
            $fullUrl = "{$this->url}/{$endpoint}";
        }

        $response = Http::withHeaders($this->headers);

        if ($noToken) {
            switch ($method) {
                case 'put':
                    $response = $response->put($fullUrl, $body);
                    break;

                default:
                    $response = $response->post($fullUrl, $body);
                    break;
            }
        } else {
            $response = $response->withToken($token)
                                ->post($fullUrl, $body);
        }

        Log::info("Posting to {$fullUrl}");
        Log::info($this->headers);
        Log::info($response);

        if (count($response->json())) {
            return $response->json();
        }

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }
}
