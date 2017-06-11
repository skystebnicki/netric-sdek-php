<?php

namespace NetricSDKTest\Entity;

use PHPUnit_Framework_TestCase;
use NetricSDK\DataMapper\MemcachedDataMapper;
use NetricSDK\Entity\Entity;


class MemcachedDataMapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * Datamapper for storing remote data locally
     *
     * @var MemcachedDataMapper
     */
    private $dataMapper = null;

    protected function setUp()
    {
        $this->dataMapper = new MemcachedDataMapper("testappname","memcached");
    }

    public function testSaveEntity()
    {
        $customer = new Entity("customer");
        $customer->name = "Test Company";
        $customer->id = "1234";
        $ret = $this->dataMapper->saveEntity($customer);
        $this->assertTrue($ret);
    }

    public function testGetEntity()
    {
        $customer = new Entity("customer");
        $customer->name = "Test Company";
        $customer->id = "1234";
        $ret = $this->dataMapper->saveEntity($customer);

        // Load the customer into a new entity and verify the data
        $loadedCustomerData = $this->dataMapper->getEntity("customer", "1234");
        $this->assertNotNull($loadedCustomerData);
        $this->assertEquals($customer->name, $loadedCustomerData->name);
        $this->assertEquals($customer->id, $loadedCustomerData->id);
    }

}