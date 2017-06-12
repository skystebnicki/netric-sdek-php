<?php
namespace NetricSDK\Cache;

/**
 * Datamapper used to store remote data locally
 */
class MemcachedCache implements CacheInterface
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
     * Save data to cache
     *
     * @param string $key Unique key to save data to
     * @param string|int|array $data The data to save
     * @param int $expires Expires in number of milliseconds, 0 = never
     * @return bool true on success, false on failure
     */
    public function set($key, $data, $expires=0)
    {
        return $this->memCached->set(
            $this->applicationId . "-" . $key,
            $data,
            $expires
        );
    }

    /**
     * Get data from cache
     *
     * @param string $key Unique key to save data to
     * @return mixed|null Value for key or null if not found
     */
    public function get($key)
    {
        return $this->memCached->get($this->applicationId . "-" . $key);
    }
}