<?php

namespace BigSea\Gulfstream\API\CacheHandler;

class NullAdapter implements CacheInterface
{
    public function get($key)
    {
        return null;
    }

    public function set($key, $data = null)
    {
        return null;
    }

    public function delete($key)
    {
        return null;
    }
}