<?php
namespace NetricSDK;

use NetricSDK\EntityCollection\EntityCollection;
use NetricSDK\Entity\Entity;

interface ApiCallerInterface
{
	/**
	 * Save an entity to the backend
	 *
	 * @param Entity $entity Either a new entity (with no id value set) or an existing entity
	 * @return bool true on success, false on failure
	 */
	public function saveEntity(Entity $entity);

	/**
	 * Retrieve an entity by id
	 *
	 * @param string $objType The name of object this entity represents - like 'user'
	 * @param string $id the Unique id of the entity to load
	 * @return Entity the populated entity if found, or null if it does not exist
	 */
	public function getEntity($objType, $id);

	/**
	 * Retrieve an entity by id
	 *
	 * @param string $objType The name of object this entity represents - like 'user'
	 * @param string $id the Unique id of the entity to load
	 * @param array $namespaceCondtiions Optional namesapce conditions
	 * @return Entity the populated entity if found, or null if it does not exist
	 */
	public function getEntityByUniqueName($objType, $uname, array $namespaceCondtiions = []);

	/**
	 * Delete an entity from the backend
	 *
	 * @param Entity $entity Either a new entity (with no id value set) or an existing entity
	 * @return bool true on success, false on failure
	 * @throws \Exception if an invalid entity was passed
	 */
	public function deleteEntity(Entity $entity);

	/**
	 * Query the backend for entities that match the passed query conditions and set the collection
	 *
	 * @param EntityCollection $collection A collection to query and set entities into
	 * @return int The number of entities retrieved in the current page
	 */
	public function loadCollection(EntityCollection $collection);

	/**
	 * Get object definition based on an object type
	 *
	 * @param string $objType The object type name
	 * @param string $fieldName The field name to get grouping data for
	 * @return \Netric\Models\EntityGrouping[]
	 */
	public function getEntityGroupings($objType, $fieldName);
}
