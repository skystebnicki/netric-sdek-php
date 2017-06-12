<?php
namespace NetricSDK;

use NetricSDK\EntityCollection\EntityCollection;
use NetricSDK\Entity\Entity;
use NetricSDK\Entity\EntityIdentityMapper;
use NetricSDK\Cache\MemcachedCache;

/**
 * Main API service
 */
class NetricApi
{
	/**
	 * Instance of an API requestor
	 *
	 * @var ApiCaller
	 */
	private $apiCaller = null;

	/**
	 * Identity ampper for loading entities and keeping only one instance in memeory at once
	 *
	 * @var EntityIdentityMapper
	 */
	private $identityMapper = null;

	/**
	 * Constructor will setup API connection credentials
	 *
	 * @param string $server The server we are connecting to
	 * @param string $applicationId A unique ID supplied to grant access to the API for an application service user
	 * @param string $applicationKey The private key used to sign all requests
     * @param array $cacheConfig Optional configuration to use memcached or redis caching
	 */
	public function __construct($server, $applicationId, $applicationKey, array $cacheConfig = null)
	{
	    // If cache config was passed then setup a CacheInterface to cache api responses
        $cache = null;
		if ($cacheConfig) {
		    if ($cacheConfig['type'] === 'memcached' && isset($cacheConfig['server'])) {
		        $cache = new MemcachedCache($applicationId, $cacheConfig['server']);
            }
        }
        $this->apiCaller = new ApiCaller($server, $applicationId, $applicationKey, $cache);

        $this->identityMapper = new EntityIdentityMapper($this->apiCaller);
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
		return $this->identityMapper->getById($objType, $id);
	}

	/**
	 * Load an entity from the server by uname
	 *
	 * @param string $objType The type of entity to get
	 * @param string $id The unique id of the entity to load
	 * @return Entity
	 */
	public function getEntityByUniqueName($objType, $uniqueName, $namespaceConditions = [])
	{
		return $this->identityMapper->getByUniqueName($objType, $uniqueName, $namespaceConditions);
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
