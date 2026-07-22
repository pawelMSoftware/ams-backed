<?php

namespace App\AMS;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class AMSRedis
{
    /**
     * @param string $warehouseName
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $warehouseName, string $key, mixed $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $key = self::getKeyName($warehouseName, $key);
        Redis::set($key, $value);
    }

    /**
     * @param string $warehouseName
     * @param string $key
     * @return string
     */
    public function getKeyName(string $warehouseName, string $key): string
    {
        return self::getWarehousePrefix($warehouseName) . ':' . $key;
    }

    /**
     * @todo consider if it's ok to return array instead of object
     *  - it also refers to the method set() and converting array into json
     * @param string $warehouseName
     * @param string $key
     * @param bool $arrayForJson - returns array instead of object by default if key is a json string
     * @return string|array|object
     */
    public function get(string $warehouseName, string $key, bool $arrayForJson = false): string|array|object|null
    {
        $key = self::getWarehousePrefix($warehouseName) . ':' . $key;
        $val = Redis::get($key);
        return $this->isJSON($val) ? json_decode($val, $arrayForJson) : $val;
    }

    /**
     * @param string $warehouseName
     * @return string
     */
    public function getWarehousePrefix(string $warehouseName): string
    {
        // $redisPrefix = config('database.redis.default.prefix', 'ams:');
        return match($warehouseName) {
            'category' => 'category',
            'redistest' => 'redistest',
            default => '___',
        };
    }

    public function isJSON(?string $json): bool
    {
        if (version_compare(PHP_VERSION, '8.3') >= 0) {
            return json_validate($json);
        } else {
            return !is_null(json_decode($json));
        }
    }
}
