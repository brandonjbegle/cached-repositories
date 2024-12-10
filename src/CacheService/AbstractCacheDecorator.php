<?php

namespace BrandonJBegle\CachedRepositories\CacheService;

use BrandonJBegle\CachedRepositories\Contracts\CacheInterface;

abstract class AbstractCacheDecorator
{
    protected $cache;

    protected $tags;

    protected $repository;

    public function __construct(CacheInterface $cache, $repository)
    {
        $this->cache = $cache;
        $this->repository = $repository;
    }

    /**
     * Clear all items from the cache
     *
     * @return void
     */
    public function flush()
    {
        $this->cache->flush();
    }

    public function tags($tags)
    {
        return $this->cache->tags($tags);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->repository, $name], $arguments);
    }

    protected function result($cacheName, $repository, $functionName, $args = [])
    {
        if ($this->cache->has($cacheName)) {
            return $this->cache->get($cacheName);
        }

        $result = call_user_func([$repository, $functionName], ...$args);

        $this->cache->put($cacheName, $result);

        return $result;
    }
}
