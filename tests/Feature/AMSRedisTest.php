<?php

namespace Tests\Feature;

use Tests\AMSTestCase;

class AMSRedisTest extends AMSTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_warehouse_prefix()
    {
        $warehousePrefix = \AMSRedis::getWarehousePrefix('category');
        $this->assertEquals('category', $warehousePrefix);
    }

    public function test_get_key_name()
    {
        $keyName = \AMSRedis::getKeyName('category', 'key1');
        $this->assertEquals('category:key1', $keyName);
    }

    /**
     * @return void
     */
    public function test_is_json()
    {
        $this->assertTrue(\AMSRedis::isJSON('{"1": "one"}'));
        $this->assertFalse(\AMSRedis::isJSON('sth'));
    }

    /**
     * @depends test_is_json
     *
     * @return void
     */
    public function test_get()
    {
        \AMSRedis::set('redistest', 'key1', 'value1');
        \AMSRedis::set('redistest', 'key2', 'value2');
        \AMSRedis::set('redistest', 'key3', '{"testKey":"testVal"}');

        $this->assertEquals('value1', \AMSRedis::get('redistest', 'key1'));
        $this->assertEquals('value2', \AMSRedis::get('redistest', 'key2'));
        $this->assertNull(\AMSRedis::get('redistest', 'keyX'));

        $key3 = \AMSRedis::get('redistest', 'key3', true);
        $this->assertIsArray($key3);
        $this->assertArrayHasKey('testKey', $key3);

        $key4 = \AMSRedis::get('redistest', 'key3');
        $this->assertIsObject($key4);
        $this->assertObjectHasAttribute('testKey', $key4);
        $this->assertEquals('testVal', $key4->testKey);
    }
}
