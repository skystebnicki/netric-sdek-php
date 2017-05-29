<?php
namespace NetricSDKTest\EntityCollection;

use PHPUnit_Framework_TestCase;
use NetricSDK\EntityCollection\Where;

class WhereTest extends PHPUnit_Framework_TestCase
{
	public function testEquals()
	{
		$where = new Where("field");
		$where->equals('test');
		$this->assertEquals($where->operator, Where::OPERATOR_EQUAL_TO);
	}
}
