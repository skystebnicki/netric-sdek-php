<?php

namespace NetricSDKTest\Entity;

use PHPUnit_Framework_TestCase;
use NetricSDK\Cache\MemcachedCache;


/**
 * Class MemcachedCacheTest
 * @group integration
 */
class MemcachedCacheTest extends PHPUnit_Framework_TestCase
{
    public function testSet()
    {
        $cache = new MemcachedCache("appid", "memcached");
        $this->assertTrue($cache->set("testkey", ['some', 'values'], 3000));
    }

    public function testGet()
    {
        $testArray = ['some', 'values'];
        $cache = new MemcachedCache("appid", "memcached");
        $cache->set("testkey", $testArray, 3000);
        $cachedData = $cache->get('testkey');
        $this->assertEquals($testArray, $cachedData);
    }
}