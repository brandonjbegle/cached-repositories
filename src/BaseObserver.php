<?php

namespace BrandonJBegle\CachedRepositories;

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
