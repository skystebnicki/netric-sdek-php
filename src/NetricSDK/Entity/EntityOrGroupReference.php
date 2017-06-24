<?php
namespace NetricSDK\Entity;

/**
 * An entity reference field contains an id, and cached name
 *
 * This can either be a reference to an entity or a grouping
 */
class EntityOrGroupReference
{
    /**
     * The unique id of the grouping or entity
     *
     * @var string
     */
    public $id = "";

    /**
     * Cached name for the grouping or entity
     *
     * @var string
     */
    public $name = "";

    /**
     * EntityOrGroupReference constructor
     *
     * @param string $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}