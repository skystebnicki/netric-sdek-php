<?php
namespace NetricSDK\Cache;

/**
 * Define common interface for all cache
 */
interface CacheInterface
{
    /**
     * Save data to cache
     *
     * @param string $key Unique key to save data to
     * @param string|int|array $data The data to save
     * @param int $expires Expires in number of milliseconds, 0 = never
     * @return bool true on success, false on failure
     */
    public function set($key, $data, $expires=0);

    /**
     * Get data from cache
     *
     * @param string $key Unique key to save data to
     * @return mixed|null Value for key or null if not found
     */
    public function get($key);
}