<?php
namespace NetricSDKTest;

use NetricSDK\DataMapper\MemoryDataMapper;
use PHPUnit_Framework_TestCase;
use NetricSDK\ApiCaller;
use NetricSDK\Entity\EntityIdentityMapper;
use NetricSDK\Entity\Entity;

class EntityIdentityMapperTest extends PHPUnit_Framework_TestCase
{
    public function testGetEntity()
    {
        // Creaate a test entity
        $entity = new Entity("task");
        $entity->name = "my test";
        $entity->id = 1001;

        // Mock the apicaller
        $apiCaller = $this->getMockBuilder(ApiCaller::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $apiCaller->method('getEntity')
            ->willReturn($entity);

        $identityMapper = new EntityIdentityMapper($apiCaller);
        $loadedEntity = $identityMapper->getById("task", $entity->id);

        $this->assertEquals($entity, $loadedEntity);
    }

    public function testGetEntityByUniqueName()
    {
        // Creaate a test entity
        $entity = new Entity("task");
        $entity->name = "my test";
        $entity->id = 1001;
        $entity->uname = "fake_uname";

        // Mock the apicaller
        $apiCaller = $this->getMockBuilder(ApiCaller::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $apiCaller->method('getEntityByUniqueName')
            ->willReturn($entity);

        $identityMapper = new EntityIdentityMapper($apiCaller);
        $loadedEntity = $identityMapper->getByUniqueName("task", $entity->uname);

        $this->assertEquals($entity, $loadedEntity);
    }
}
