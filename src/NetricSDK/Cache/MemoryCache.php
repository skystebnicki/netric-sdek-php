<?php
namespace NetricSDK\Cache;

/**
 * Cache that stores data in memory
 */
class MemoryCache implements CacheInterface
{
    /**
     * In-memory key value store
     *
     * @var array
     */
    private $cache = [];

    /**
     * Save the last key written
     *
     * @var string
     */
    public $lastKeyWritten = "";

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
        $this->cache[$key] = $data;
        $this->lastKeyWritten = $key;
        return true;
    }

    /**
     * Get data from cache
     *
     * @param string $key Unique key to save data to
     * @return mixed|null Value for key or null if not found
     */
    public function get($key)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        } else {
            return null;
        }
    }

    /**
     * Get the last entry cached - normally used for testing
     *
     * @return mixed
     */
    public function getLastEntry()
    {
        return $this->cache[$this->lastKeyWritten];
    }
}