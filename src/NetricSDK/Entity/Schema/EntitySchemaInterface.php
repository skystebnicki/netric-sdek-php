<?php
namespace NetricSDK\Entity\Schema;

use NetricSDK\Entity\Entity;

/**
 * Interface for handling different data schemas for an entity
 */
interface EntitySchemaInterface
{
    /**
     * Get the version of this entity schema
     *
     * @return int
     */
    public function getSchemaVersion();

    /**
     * Export an entity to an associative array matching schema version 1
     *
     * @param Entity $entity
     */
    public function getDataFromValues(Entity $entity);

    /**
     * Set values in an entitty from an associative array
     *
     * @param Entity $entity
     * @param array $data
     */
    public function setValuesFromData(Entity $entity, array $data);
}
