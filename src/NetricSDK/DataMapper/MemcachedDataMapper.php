<?php
namespace NetricSDK\DataMapper;

use NetricSDK\Entity\Entity;
use NetricSDK\Entity\EntityFactory;
use NetricSDK\EntityCollection\EntityCollection;

/**
 * Datamapper used to store remote data locally
 */
class MemcachedDataMapper implements DataMapperInterface
{
    /**
     * The memcached server to connect to
     *
     * @var string
     */
    private $server = "";

    /**
     * The port to connect to
     *
     * @var int
     */
    private $port = 0;

    /**
     * Instance of Memcached
     *
     * @var \Memcached
     */
    private $memCached = null;

    /**
     * Unique applicationId used to prefix all keys to avoid collisions
     *
     * @var string
     */
    private $applicationId = "";

    /**
     * Default expiration for data
     *
     * We don't want to store this for too long because it may change
     * on the server and the point of the cache datamapper is just to
     * provide temporary relief for the server in the case of many
     * requests for the same data being sent over and over.
     *
     * @var int
     */
    private $dataExpires = 3000;

    /**
     * MemcachedDataMapper constructor.
     *
     * @param string $applicationId Required unique id of current application
     * @param string $server Server name or ip
     * @param int $port The port to connect to
     */
    public function __construct($applicationId, $server, $port = 11211)
    {
        $this->applicationId = $applicationId;
        $this->server = $server;
        $this->port = $port;

        $this->memCached = new \Memcached();

        // Make sure servers are not already added
        if (!count($this->memCached->getServerList())) {
            if (is_array($server)) {
                $servers = array();
                foreach ($server as $svr)
                    $servers[] = array($svr, 11211, 100);

                $this->memCached->addServers($servers);
            } else {
                $this->memCached->addServer($server, 11211);
            }
        }
    }

    /**
     * Save an entity to the datastore
     *
     * @param Entity $entity
     * @return bool true on success, false on failure
     */
    public function saveEntity(Entity $entity)
    {
        $values = $entity->getValues();
        $objType = $entity->getType();

        $data = array('obj_type' => $objType);

        foreach ($values as $fieldName=>$value) {
            $data[$fieldName] = $value;
        }

        return $this->memCached->set(
            $this->getKeyForEntity($objType, $entity->id),
            json_encode($data),
            $this->dataExpires
        );
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
        $data =  $this->memCached->get($this->getKeyForEntity($objType, $id));

        if ($data) {
            $dataDecoded = json_decode($data, true);
            return $this->loadEntityFromData($dataDecoded);
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
        $hash = $collection->getHash();

        $cachedData = $this->memCached->get($this->applicationId . "-coll-" . $hash);

        if ($cachedData) {
            $collection->clearEntities();
            $collection->setTotalNum($cachedData['total_num']);
            foreach ($cachedData['entities'] as $entityData) {
                $entity = $this->loadEntityFromData($entityData);
                $collection->addEntity($entity);
            }
            return count($cachedData['entities']);
        } else {
            return -1;
        }

    }

    /**
     * Save a collection to cache
     *
     * @param EntityCollection $collection
     * @return bool true on success, false on failure
     */
    public function saveCollection(EntityCollection $collection)
    {
        $hash = $collection->getHash();
        $data = array('total_num'=>$collection->getTotalNum());


        return $this->memCached->set(
            $this->applicationId . "-coll-" . $hash,
            json_encode($data)
        );
    }

    /**
     * Generate a unique key for an entity
     *
     * @param string $objType Object type of entity to retrieve
     * @param string $id Unqiue id of the entity to load
     * @return string
     */
    private function getKeyForEntity($objType, $id)
    {
        return $this->applicationId . "-" . $objType . '-' . $id;
    }

    /**
     * Initialze all the properties in an entity from data
     *
     * @param array $data The data to load into the entity
     * @return Entity An initialized entity from the data
     */
    private function loadEntityFromData(array $data)
    {
        if (!isset($data['obj_type'])|| !isset($data['id'])) {
            return null;
        }

        $entity = EntityFactory::factory($data['obj_type'], $data['id']);

        foreach ($data as $fieldName=>$fieldValue) {
            $entity->$fieldName = $fieldValue;
        }

        return $entity;
    }
}