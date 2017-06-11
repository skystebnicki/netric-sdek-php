<?php
namespace NetricSDKTest;

use PHPUnit_Framework_TestCase;
use NetricSDK\NetricApi;
use NetricSDK\Entity\Entity;

/**
 * Integration test to make sure the NetricApi service class works as expected
 *
 * @group integration
 */
class NetricApiTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Instance of NetricApi
	 *
	 * @var NetricApi
	 */
	private $netricAPi = null;

	/**
	 * List of test entities created that will need to be cleaned up
	 *
	 * @param array
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

		$this->netricApi = new NetricApi("http://integ.netric.com", $clientId, $clientSecret);
	}

	/**
	 * Cleanup leftover entities we created for tests
	 */
	public function tearDown()
	{
		foreach ($this->testEntities as $entity) {
			$this->netricApi->deleteEntity($entity);
		}
	}

	/**
	 * Make sure that the API can create a collection for querying entities
	 */
	public function testCreateEntityCollection()
	{
		$collection = $this->netricApi->createEntityCollection("task");
		$this->assertInstanceOf('\NetricSDK\EntityCollection\EntityCollection', $collection);
	}

	/**
	 * Validate that we can get an entity from the API
	 */
	public function testGetEntity()
	{
		// First create a dummy task to get
		$task = new Entity("task");
		$task->name = "test";
		$this->netricApi->saveEntity($task);

		// Queue it for cleanup in the tearDown function
		$this->testEntities[] = $task;

		// Now make sure we can retrieve the task from the API
		$taskFromServer = $this->netricApi->getEntity("task", $task->id);
		$this->assertEquals($task->name, $taskFromServer->name);
	}

	/**
	 * Retrieve an entity by id
	 */
	public function testGetEntityByUniqueName()
	{
		$page = new Entity("cms_page");
		$page->name = "testGetEntityByUniqueName";
		$this->netricApi->saveEntity($page);
		$this->testEntities[] = $page;

		$pageFromServer = $this->netricApi->getEntityByUniqueName("cms_page", $page->uname);
		$this->assertEquals($page->name, $pageFromServer->name);
	}

	/**
	 * Validate that we can get an entity grouping from the API
	 */
	public function testGetEntityGroupings()
	{
		// Now make sure we can retrieve the task from the API
		$entityGroupings = $this->netricApi->getEntityGroupings("user", "groups");
		$this->assertGreaterThanOrEqual(1, $entityGroupings);
	}

	/**
	 * Make sure we can save an entity through the API
	 */
	public function testSaveEntity()
	{
		// First make an entity save
		$task = new Entity("task");
		$task->name = "test";
		$this->netricApi->saveEntity($task);

		// Queue it for deletion (cleanup)
		$this->testEntities[] = $task;

		// Make sure that the server populated requried fields
		$this->assertTrue(isset($task->id));
		$this->assertNotEmpty($task->id);
	}
}