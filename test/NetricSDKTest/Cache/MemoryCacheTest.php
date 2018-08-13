<?php

namespace NetricSDKTest\Entity;

use PHPUnit_Framework_TestCase;
use NetricSDK\Cache\MemoryCache;

/**
 * Class MemcachedCacheTest
 * @group integration
 */
class MemoryCacheTest extends PHPUnit_Framework_TestCase
{
    public function testSet()
    {
        $cache = new MemoryCache();
        $this->assertTrue($cache->set("testkey", ['some', 'values'], 3000));
    }

    public function testGet()
    {
        $testArray = ['some', 'values'];
        $cache = new MemoryCache();
        $cache->set("testkey", $testArray, 3000);
        $cachedData = $cache->get('testkey');
        $this->assertEquals($testArray, $cachedData);
    }
}
