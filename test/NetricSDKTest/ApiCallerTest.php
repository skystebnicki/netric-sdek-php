<?php
namespace NetricSDKTest;

use PHPUnit_Framework_TestCase;
use NetricSDK\ApiCaller;
use NetricSDK\Entity\Entity;
use NetricSDK\EntityCollection\EntityCollection;

/**
 * Integration tests for the API caller
 *
 * @group integration
 */
class ApiCallerTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Instance of API caller to test
	 */
	private $apiCaller = null;

	/**
	 * List of test entities created that will need to be cleaned up
	 */
	private $testEntities = array();

	/**
	 * Initialize an ApiCaller instance that will call the devel instance of netric
	 */
	public function setUp()
	{
		// In netric the client is a user of type=service
		$clientId = "test@netric.com";
		// The clientSecret is the user's password
		$clientSecret = "password";

		$this->apiCaller = new ApiCaller("http://devel.netric.com", $clientId, $clientSecret);
	}

	public function tearDown()
	{
		foreach ($this->testEntities as $entity) {
			$this->apiCaller->deleteEntity($entity);
		}
	}

	public function testGetAuthToken()
	{
		$ret = $this->apiCaller->getAuthToken();
		$this->assertNotNull($ret);
	}

	/**
	 * Save an entity to the backend
	 */
	public function testSaveEntity()
	{
		$task = new Entity("task");
		$task->name = "test";
		$this->apiCaller->saveEntity($task);
		$this->testEntities[] = $task;

		$this->assertTrue(isset($task->id));
		$this->assertNotEmpty($task->id);
	}

	public function testDeleteEntity()
	{
		$task = new Entity("task");
		$task->name = "test";
		$this->apiCaller->saveEntity($task);

		// Now delete it
		$ret = $this->apiCaller->deleteEntity($task);
		$this->assertTrue($ret);
	}

	/**
	 * Retrieve an entity by id
	 */
	public function testGetEntity()
	{
		$task = new Entity("task");
		$task->name = "test";
		$this->apiCaller->saveEntity($task);
		$this->testEntities[] = $task;

		$taskFromServer = $this->apiCaller->getEntity("task", $task->id);
		$this->assertEquals($task->name, $taskFromServer->name);
	}

	public function testGetEntityGroupings()
	{
		// Users will always have at least one group
		$groupings = $this->apiCaller->getEntityGroupings("user", "groups");
		$this->assertGreaterThanOrEqual(1, $groupings);
	}

	/**
	 * Query the backend for entities that match the passed query conditions and set the collection
	 */
	public function testLoadCollection()
	{
		$uniqueTaskname = "testapicaller";

		$task = new Entity("task");
		$task->name = $uniqueTaskname;
		$this->apiCaller->saveEntity($task);
		$this->testEntities[] = $task;

		$collection = new EntityCollection("task");
		$collection->where("name")->equals($uniqueTaskname);
		$collection->andWhere("id")->equals($task->id);
		$this->apiCaller->loadCollection($collection);

		$this->assertEquals(1, $collection->getTotalNum());
		$entity = $collection->getEntity();
		$this->assertEquals($uniqueTaskname, $entity->name);
	}

	
}
