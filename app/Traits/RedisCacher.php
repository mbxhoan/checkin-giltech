<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

trait RedisCacher
{
    public static function getRedis($prefix, $id, $returnType = "json")
    {
        try {
            $cache = Redis::get("{$prefix}:{$id}");
            return self::format($cache, $returnType);
        } catch (Exception $e) {
            Log::alert($e);
        }

        return null;
    }

    public static function setRedis($prefix, $id, $data, $expirationMinutes = null)
    {
        $key = "{$prefix}:{$id}";

        try {
            if ($expirationMinutes) {
                Redis::setex($key, $expirationMinutes*60, $data);
            } else {
                Redis::set($key, $data);
            }

            return true;
        } catch (Exception $e) {
            Log::alert($e);
        }

        return false;
    }

    public static function updateRedis($prefix, $id, $data, $expirationMinutes = null)
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }

        try {
            // $data = json_encode($data);
            self::deleteRedis($prefix, $id);
            self::setRedis($prefix, $id, $data, $expirationMinutes);
            return true;
        } catch (Exception $e) {
            Log::alert($e);
        }

        return false;
    }

    public static function deleteRedis($prefix, $id)
    {
        try {
            $key = "{$prefix}:{$id}";
            Redis::del($key);
            return true;
        } catch (Exception $e) {
            Log::alert($e);
        }

        return false;
    }

    private static function format($result, $type)
    {
        switch ($type) {
            case "objectArray":
                return json_decode($result, FALSE);
            case "array":
                return json_decode($result, true) ?? [];
            default:
                /* raw */
                return $result;
        }

        return null;
    }
}
