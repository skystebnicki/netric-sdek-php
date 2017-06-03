<?php
namespace NetricSDK;

use NetricSDK\EntityCollection\EntityCollection;
use NetricSDK\Entity\Entity;

/**
 * Main API service
 */
class NetricApi
{
	/**
	 * Instance of an API requestor
	 *
	 * @param ApiCaller
	 */
	private $apiCaller = null;

	/**
	 * Constructor will setup API connection credentials
	 *
	 * @param string $server The server we are connecting to
	 * @param string $applicationId A unique ID supplied to grant access to the API for an application service user
	 * @param string $applicationKey The private key used to sign all requests
	 */
	public function __construct($server, $applicationId, $applicationKey)
	{
		$this->apiCaller = new ApiCaller($server, $applicationId, $applicationKey);
	}

	/**
	 * Craete a new collection
	 *
	 * @param string $objType The type of entity we are querying
	 * @return EntityCollection
	 */
	public function createEntityCollection($objType)
	{
		$collection = new EntityCollection($objType);
		$collection->setApiCaller($this->apiCaller);
		return $collection;
	}

	/**
	 * Load an entity from the server
	 *
	 * @param string $objType The type of entity to get
	 * @param string $id The unique id of the entity to load
	 * @return Entity
	 */
	public function getEntity($objType, $id)
	{
		return $this->apiCaller->getEntity($objType, $id);
	}

	/**
	 * Retrieve an entity groupings from the server
	 *
	 * @param string $objType The type of entity to get
	 * @param string $fieldName The grouping field
	 * @param return Entity\EntityGrouping[]
	 */
	public function getEntityGroupings($objType, $fieldName)
	{
		return $this->apiCaller->getEntityGroupings($objType, $fieldName);
	}

	/**
	 * Save an entity to the server
	 *
	 * @param Entity $entity Any entity to save
	 * @return bool true on success, false on failure
	 */
	public function saveEntity(Entity $entity)
	{
		return $this->apiCaller->saveEntity($entity);
	}

	/**
	 * Delete an entity from the API
	 *
	 * @param Entity $entity The entity to delete
	 * @return bool true on success, false on failure
	 */
	public function deleteEntity(Entity $entity)
	{
		return $this->apiCaller->deleteEntity($entity);
	}
}
