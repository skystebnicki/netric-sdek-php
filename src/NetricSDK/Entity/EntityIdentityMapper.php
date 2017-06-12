<?php
namespace NetricSDK\Entity;

use NetricSDK\ApiCaller;

/**
 * Make sure we have only one instance of an entity loaded at any given time
 */
class EntityIdentityMapper
{
	/**
	 * Used to make API calls to the server 	
	 *
	 * @param ApiCaller 
	 */
	private $apiCaller = null;

	/**
	 * Keep loaded entities in memeory so we only ever have one instance of each entity
	 *
	 * @var array('obj_type'=>array('id'=>Entity))
	 */
	private $loadedEntities = [];

	/**
	 * Store a map from a unique name to an id
	 *
	 * @var array('obj_type'=>array('uname'=>'id'))
	 */
	private $uniqueNamesToIDs = [];


	/**
	 * Identity mapper constructor
	 *
	 * @param ApiCaller $apiCaller Used to make API calls to the server
	 */
	public function __construct(ApiCaller $apiCaller)
	{
		$this->apiCaller = $apiCaller;
	}

	/**
	 * Retrieve an entity by id
	 *
	 * @param string $objType The name of object this entity represents - like 'user'
	 * @param string $id the Unique id of the entity to load
	 * @return Entity the populated entity if found, or null if it does not exist
	 */
	public function getById($objType, $id)
	{
	    // First check if we have the entity loaded in memory
		if (isset($this->loadedEntities[$objType])) {
			if (isset($this->loadedEntities[$objType][$id])) {
				return $this->loadedEntities[$objType][$id];
			}
		} else {
			// Initialize the array since we'll be saving it later
			$this->loadedEntities[$objType] = [];
		}

        $entity = $this->apiCaller->getEntity($objType, $id);

		// Cache for future calls so we can keep it in memory
		if ($entity) {
			$this->loadedEntities[$objType][$id] = $entity;
		}

		return $entity;
	}

	/**
	 * Retrieve an entity by id
	 *
	 * @param string $objType The name of object this entity represents - like 'user'
	 * @param string $id the Unique id of the entity to load
	 * @param array $namespaceCondtiions Optional namesapce conditions
	 * @return Entity the populated entity if found, or null if it does not exist
	 */
	public function getByUniqueName($objType, $uniqueName, array $namespaceConditions = [])
	{
		if (isset($this->uniqueNamesToIDs[$objType])) {
			if (isset($this->uniqueNamesToIDs[$objType][$uniqueName])) {
				// Get by ID
				return $this->getById($objType, $this->uniqueNamesToIDs[$objType][$uniqueName]);
			}
		} else {
			// Initialize the array since we'll be saving it later
			$this->uniqueNamesToIDs[$objType] = [];
		}

		$entity = $this->apiCaller->getEntityByUniqueName($objType, $uniqueName, $namespaceConditions);

		// Cache for future calls so we can keep it in memory
		if ($entity) {
			$this->loadedEntities[$objType][$entity->id] = $entity;
			$this->uniqueNamesToIDs[$objType][$uniqueName] = $entity->id;
		}

		return $entity;
	}
}