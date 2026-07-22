<?php

namespace App\AMS;

use Illuminate\Support\Facades\Redis;

class AMSRedis
{
    public function set(string $warehouseName, string $key, mixed $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $key = self::getKeyName($warehouseName, $key);
        Redis::set($key, $value);
    }

    public function getKeyName(string $warehouseName, string $key): string
    {
        return self::getWarehousePrefix($warehouseName).':'.$key;
    }

    /**
     * @todo consider if it's ok to return array instead of object
     *  - it also refers to the method set() and converting array into json
     *
     * @param  bool  $arrayForJson  - returns array instead of object by default if key is a json string
     */
    public function get(string $warehouseName, string $key, bool $arrayForJson = false): string|array|object|null
    {
        $key = self::getWarehousePrefix($warehouseName).':'.$key;
        $val = Redis::get($key);

        return $this->isJSON($val) ? json_decode($val, $arrayForJson) : $val;
    }

    public function getWarehousePrefix(string $warehouseName): string
    {
        // $redisPrefix = config('database.redis.default.prefix', 'ams:');
        return match ($warehouseName) {
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
            return ! is_null(json_decode($json));
        }
    }
}
