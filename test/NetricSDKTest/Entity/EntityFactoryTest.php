<?php

namespace NetricSDKTest\Entity;

use PHPUnit_Framework_TestCase;
use NetricSDK\Entity\EntityFactory;

class EntityFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider objTypesProvider
     */
    public function testFactoryCustomer($objType, $expectedInstance)
    {
        $entity = EntityFactory::factory($objType);
        $this->assertInstanceOf($expectedInstance, $entity);
    }

    /**
     * Add each custom object type we are loading here
     */
    public function objTypesProvider()
    {
        return [
            ["customer", 'NetricSDK\Entity\Type\CustomerEntity'],
            ["content_feed_post", 'NetricSDK\Entity\Type\ContentFeedPostEntity'],
            ["infocenter_document", 'NetricSDK\Entity\Type\InfocenterDocumentEntity'],
            ["default", 'NetricSDK\Entity\Entity'],
        ];
    }
}
