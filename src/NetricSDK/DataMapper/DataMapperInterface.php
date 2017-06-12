<?php
namespace NetricSDK\DataMapper;
use NetricSDK\Entity\Entity;
use NetricSDK\EntityCollection\EntityCollection;

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

    /**
     * Query the backend for entities that match the passed query conditions and set the collection
     *
     * @param EntityCollection $collection A collection to query and set entities into
     * @return int The number of entities retrieved in the current page
     */
    public function loadCollection(EntityCollection $collection);
}