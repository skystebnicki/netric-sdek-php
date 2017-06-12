<?php
namespace NetricSDK\DataMapper;

use NetricSDK\Entity\Entity;
use NetricSDK\EntityCollection\EntityCollection;

/**
 * Cache data in local memory
 */
class MemoryDataMapper implements DataMapperInterface
{
    /**
     * Memory cache
     *
     * @var array
     */
    private $cache = [];

    /**
     * Save an entity to the datastore
     *
     * @param Entity $entity
     * @return bool true on success, false on failure
     */
    public function saveEntity(Entity $entity)
    {
        if (!isset($this->cache['entities'])) {
            $this->cache['entities'] = [];
        }

        if (!isset($this->cache['entities'][$entity->getType()])) {
            $this->cache['entities'][$entity->getType()] = [];
        }

        $this->cache['entities'][$entity->getType()][$entity->id] = $entity;
    }

    /**
     * Get an entity from the datastore
     *
     * @param string $objType The type of entity to load
     * @param string $id The unique id of the entity to load
     * @return Entity
     */
    public function getEntity($objType, $id)
    {
        // If cached then return the entity
        if (isset($this->cache['entities'])) {
            if (isset($this->cache['entities'][$objType])) {
                if (isset($this->cache['entities'][$objType][$id])) {
                    return $this->cache['entities'][$objType][$id];
                }
            }
        }

        return null;
    }

    /**
     * Query the backend for entities that match the passed query conditions and set the collection
     *
     * @param EntityCollection $collection A collection to query and set entities into
     * @return int The number of entities retrieved in the current page or -1 if not cached
     */
    public function loadCollection(EntityCollection $collection)
    {
        return -1;
    }
}