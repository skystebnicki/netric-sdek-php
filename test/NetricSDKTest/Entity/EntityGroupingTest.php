<?php
namespace NetricSDKTest\Entity;

use PHPUnit_Framework_TestCase;
use NetricSDK\Entity\EntityGrouping;

class EntityGroupingTest extends PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $grouping = new EntityGrouping();
        $grouping->id = 1;
        $grouping->title = "test";
        $data = $grouping->toArray();
        $this->assertEquals(1, $data['id']);
        $this->assertEquals("test", $data['title']);
    }
}
