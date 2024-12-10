<?php

namespace BrandonJBegle\CachedRepositories\CacheService;

use BrandonJBegle\CachedRepositories\Contracts\CacheInterface;
use Illuminate\Cache\CacheManager;

class LaravelCache implements CacheInterface
{
    protected $cache;

    protected $cacheKey;

    protected $minutes;

    protected $tags;

    /**
     * Cache constructor
     */
    public function __construct(CacheManager $cache, $minutes = null)
    {
        $this->cache = $cache;
        $this->minutes = $minutes;
    }

    /**
     * Set tags for this Cache Store
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Set minutes for this Cache Store
     */
    public function setMinutes($minutes)
    {
        $this->minutes = $minutes;
    }

    /**
     * Set Cache Store tags if driver supports tagging
     * fallback to normal cache
     *
     *
     * @return CacheManager
     */
    public function tags($tags)
    {
        if ($this->cache->driver()->getStore() instanceof \Illuminate\Cache\TaggableStore) {
            return $this->cache->tags($tags);
        }

        return $this->cache;
    }

    /**
     * Retrieve data from cache
     *
     * @param  string  $key  Cache item key
     * @return mixed PHP data result of cache
     */
    public function get($key)
    {
        return $this->tags($this->tags)->get($key);
    }

    /**
     * Pull item from the cache and delete it
     *
     * @param  string  $key  cache item key
     * @return mixed PHP results of cache
     */
    public function pull($key)
    {
        return $this->tags($this->tags)->pull($key);
    }

    /**
     * Add data to the cache
     *
     * @param  string  $key  Cache item key
     * @param  mixed  $value  The data to store
     * @param  int  $minutes  Cache item lifetime in minutes
     * @return mixed $value variable returned for convenience
     */
    public function put($key, $value, $minutes = null)
    {
        if (is_null($minutes)) {
            $minutes = $this->minutes;
        }

        return $this->tags($this->tags)->put($key, $value, $minutes);
    }

    /**
     * Add data to the cache
     * taking pagination into account
     *
     * @param int  Page of the cached items
     * @param int  Number of results per page
     * @param int  Total number of possible items
     * @param mixed    The actual items for this page
     * @param string   Cache item key
     * @param int  Cache item lifetime in minutes
     * @return mixed $items variable returned for convenience
     */
    public function putPaginated($items, $key, $minutes = null)
    {
        return $this->put($key, $items, $minutes);
    }

    /**
     * Store item in cache permanently
     *
     * @param  string  $key  Cache item key
     * @param  mixed  $value  The data to store
     */
    public function forever($key, $value)
    {
        return $this->tags($this->tags)->forever($key, $value);
    }

    /**
     * Test if item exists in cache
     * Only returns true if exists && is not expired
     *
     * @param  string  $key  Cache item key
     * @return bool If cache item exists
     */
    public function has($key)
    {
        return $this->tags($this->tags)->has($key);
    }

    /**
     * Invalidate item in cache
     *
     * @param  string  $key  Cache item key
     * @return void
     */
    public function forget($key)
    {
        $this->tags($this->tags)->forget($key);
    }

    /**
     * Clear all items from the cache
     *
     * @return void
     */
    public function flush()
    {
        $this->tags($this->tags)->flush();
    }
}
