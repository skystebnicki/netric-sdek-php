<?php
namespace NetricSDK\DataMapper;
use NetricSDK\Entity\Entity;

/**
 * Interface defines common datamapper functions for getting and setting data
 */
interface DataMapperInterface
{
    /**
     * Save an entity to the datastore
     *
     * @param Entity $entity
     * @return bool true on success, false on failure
     */
    public function saveEntity(Entity $entity);

    /**
     * Get an entity from the datastore
     *
     * @param string $objType The type of entity to load
     * @param string $id The unique id of the entity to load
     * @return Entity
     */
    public function getEntity($objType, $id);
}