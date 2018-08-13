<?php
namespace NetricSDKTest\Entity;

use PHPUnit_Framework_TestCase;
use NetricSDK\Entity\Entity;

class EntityTest extends PHPUnit_Framework_TestCase
{
    public function testGetType()
    {
        $type = "task";
        $entity = new Entity($type);
        $this->assertEquals($type, $entity->getType());
    }

    /**
     * @dataProvider fieldValuesDataProvider
     *
     * @param string $fieldName Name of the field to test
     * @param mixed $fieldValue Possible values based on different types
     */
    public function testMagicSet($fieldName, $fieldValue)
    {
        // Now set values
        $entity = new Entity('default');
        $entity->$fieldName = $fieldValue;

        // Test to make sure the values were set correctly
        $this->assertEquals($fieldValue, $entity->getValue($fieldName));
    }

    /**
     * @dataProvider fieldValuesDataProvider
     *
     * @param string $fieldName Name of the field to test
     * @param mixed $fieldValue Possible values based on different types
     */
    public function testMagicGet($fieldName, $fieldValue)
    {
        // Now set values
        $entity = new Entity('default');
        $entity->setValue($fieldName, $fieldValue);

        // Test to make sure the values were set correctly
        $this->assertEquals($fieldValue, $entity->$fieldName);
    }

    public function fieldValuesDataProvider()
    {
        return [
            // 'number'
            ['id', 1234],
            // string
            ['name', 'Test User'],
            // object
            ['owner', ['user:123' => 'Test User']],
            // object_multi
            ['associations', ['user:123'=>"Test User", 'task:432'=>'Sample Task']],
            // fkey
            ['status', ['2'=>'Active']],
            // fkey_multi
            ['groups', ['1'=>'Group 1', '2'=>'Group 2']],
        ];
    }

    public function testGetName()
    {
        $name = "test";

        // Create an entity and set the name
        $entity = new Entity('default');
        $entity->name = $name;

        $this->assertEquals($name, $entity->getName());
    }

    public function testGetTeaser()
    {
        $body = "test body";

        // Create an entity and set the name
        $entity = new Entity('default');
        $entity->body = $body;

        $this->assertEquals($body, $entity->getTeaser());
    }
}
