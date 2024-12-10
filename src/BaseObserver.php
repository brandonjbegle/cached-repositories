<?php

namespace App\Packages\CachedRepositories\src;

class BaseObserver
{
    protected $cacheInterfaces = [];

    protected function resolveCache()
    {
        if (count($this->cacheInterfaces) > 0) {
            foreach ($this->cacheInterfaces as $interface) {
                $cache = resolve($interface);
                $cache->flush();
            }
        }
    }
}
